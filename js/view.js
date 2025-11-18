/**
 * View Page JavaScript
 * Handles image printing functionality
 */

function printImage() {
    const imgSrc = document.getElementById('spotlightImage').src;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Spotlight Image</title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    background: white;
                }
                img {
                    max-width: 100%;
                    height: auto;
                    object-fit: contain;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    img {
                        max-width: 100%;
                        height: auto;
                        page-break-inside: avoid;
                    }
                }
            </style>
        </head>
        <body>
            <img src="${imgSrc}" alt="Spotlight Image" onload="window.print(); window.close();">
        </body>
        </html>
    `);
    
    printWindow.document.close();
}
