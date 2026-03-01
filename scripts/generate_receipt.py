#!/usr/bin/env python3
"""
HAMRO LABS ERP — PDF Receipt Generator
File: pdf/generate_receipt.py
Usage: python3 generate_receipt.py <input_html_file> <output_pdf_path>
Engine: WeasyPrint (pip install weasyprint)
"""

import sys
import os
import logging
from datetime import datetime

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)
log = logging.getLogger(__name__)


def generate_pdf(html_file: str, pdf_path: str) -> bool:
    """
    Convert HTML receipt file to PDF using WeasyPrint.
    Returns True on success, False on failure.
    """
    try:
        from weasyprint import HTML, CSS
        from weasyprint.text.fonts import FontConfiguration

        if not os.path.exists(html_file):
            log.error(f"HTML file not found: {html_file}")
            return False

        # Ensure output directory exists
        os.makedirs(os.path.dirname(os.path.abspath(pdf_path)), exist_ok=True)

        font_config = FontConfiguration()

        # Additional CSS for print security (anti-tamper watermark)
        extra_css = CSS(string="""
            @page {
                size: A5;
                margin: 15mm;
                @bottom-center {
                    content: "Hamro Labs ERP | Generated: """ + datetime.now().strftime('%d %b %Y %H:%M') + """ | Immutable Record";
                    font-size: 9px;
                    color: #aaa;
                }
            }
        """, font_config=font_config)

        log.info(f"Generating PDF: {html_file} → {pdf_path}")

        HTML(filename=html_file).write_pdf(
            pdf_path,
            stylesheets=[extra_css],
            font_config=font_config,
            optimize_images=True,
            presentational_hints=True,
        )

        # Verify output
        if os.path.exists(pdf_path) and os.path.getsize(pdf_path) > 0:
            size_kb = os.path.getsize(pdf_path) / 1024
            log.info(f"PDF generated successfully: {pdf_path} ({size_kb:.1f} KB)")
            return True
        else:
            log.error("PDF file was not created or is empty.")
            return False

    except ImportError:
        log.error("WeasyPrint not installed. Run: pip install weasyprint")
        return False
    except Exception as e:
        log.error(f"PDF generation error: {e}")
        return False


def main():
    if len(sys.argv) != 3:
        print("Usage: python3 generate_receipt.py <input.html> <output.pdf>")
        sys.exit(1)

    html_file = sys.argv[1]
    pdf_path  = sys.argv[2]

    success = generate_pdf(html_file, pdf_path)
    sys.exit(0 if success else 1)


if __name__ == '__main__':
    main()
