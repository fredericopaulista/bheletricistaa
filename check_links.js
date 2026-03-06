const fs = require('fs');
const path = require('path');

function getAllFiles(dirPath, arrayOfFiles) {
    const files = fs.readdirSync(dirPath);
    arrayOfFiles = arrayOfFiles || [];
    files.forEach((file) => {
        if (file === 'node_modules' || file === '.git' || file === '.next' || file === 'dist') return;
        if (fs.statSync(dirPath + '/' + file).isDirectory()) {
            arrayOfFiles = getAllFiles(dirPath + '/' + file, arrayOfFiles);
        } else {
            if (file.endsWith('.html')) {
                arrayOfFiles.push(path.join(dirPath, '/', file));
            }
        }
    });
    return arrayOfFiles;
}

const allFiles = getAllFiles(__dirname);
const allHtmlFiles = allFiles.map(f => '/' + path.relative(__dirname, f).replace(/\\/g, '/'));
const brokenLinks = {};

allFiles.forEach(file => {
    const content = fs.readFileSync(file, 'utf8');
    const relPath = '/' + path.relative(__dirname, file).replace(/\\/g, '/');

    // Simple regex for href in anchors
    const regex = /href="([^"#\s]+)"/g;
    let match;
    while ((match = regex.exec(content)) !== null) {
        let link = match[1];

        // Skip external, anchors, mailto, tel
        if (link.startsWith('http') || link.startsWith('#') || link.startsWith('mailto:') || link.startsWith('tel:') || link.startsWith('https://wa.me')) continue;

        // Normalize
        if (!link.startsWith('/')) {
            // Relative link - resolve it
            const currentDir = path.dirname(relPath);
            link = path.posix.join(currentDir, link);
        }

        // Check if exists
        let exists = allHtmlFiles.includes(link) || allHtmlFiles.includes(link + 'index.html') || allHtmlFiles.includes(link.replace(/\/$/, '') + '/index.html');

        if (!link.endsWith('.html') && !link.endsWith('/')) {
            if (allHtmlFiles.includes(link + '.html')) exists = true;
        }

        if (!exists) {
            if (!brokenLinks[link]) brokenLinks[link] = [];
            if (!brokenLinks[link].includes(relPath)) brokenLinks[link].push(relPath);
        }
    }
});

const sortedLinks = Object.keys(brokenLinks).sort();
if (sortedLinks.length === 0) {
    console.log('✅ All internal links are working correctly!');
} else {
    console.log(`❌ Found ${sortedLinks.length} unique broken links:`);
    sortedLinks.forEach(link => {
        console.log(`\nBroken Link: "${link}"`);
        console.log('Found in:');
        brokenLinks[link].forEach(f => console.log(`  - ${f}`));
    });
    process.exit(1);
}
