import PyPDF2
import os

def extract_text(pdf_path, keywords):
    if not os.path.exists(pdf_path):
        return f"File {pdf_path} not found."
    with open(pdf_path, 'rb') as f:
        reader = PyPDF2.PdfReader(f)
        results = []
        for i, page in enumerate(reader.pages):
            text = page.extract_text()
            if text:
                low_text = text.lower()
                for kw in keywords:
                    if kw.lower() in low_text:
                        results.append(f"--- Page {i+1} ---")
                        results.append(text)
                        break
        return "\n".join(results)

keywords = ["institute admin", "institute_admin", "admin dashboard", "school admin", "college admin"]
pdf_files = ["FINAL PRD TECH ROLES.pdf", "HamroLabs_SRS_v1.0 - for merge.pdf"]

output_file = "institute_admin_requirements.txt"
with open(output_file, "w", encoding="utf-8") as out:
    for pdf in pdf_files:
        out.write(f"\n========================================\n")
        out.write(f"FILE: {pdf}\n")
        out.write(f"========================================\n")
        out.write(extract_text(pdf, keywords))

print(f"Extraction complete. Results saved to {output_file}")
