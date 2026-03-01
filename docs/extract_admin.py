import PyPDF2

def extract_text(pdf_path, keywords):
    with open(pdf_path, 'rb') as f:
        reader = PyPDF2.PdfReader(f)
        results = []
        for i, page in enumerate(reader.pages):
            text = page.extract_text()
            if text:
                for kw in keywords:
                    if kw.lower() in text.lower():
                        results.append(f"--- Page {i+1} ---")
                        results.append(text)
                        break
        return "\n".join(results)

keywords = ["super admin", "superadmin", "audit dashboard", "audit"]
try:
    with open("super_admin_prd.txt", "w", encoding="utf-8") as f:
        f.write("=== PRD ===\n")
        f.write(extract_text("FINAL PRD TECH ROLES.pdf", keywords))
except Exception as e:
    print(f"Error reading PRD: {e}")

try:
    with open("super_admin_srs.txt", "w", encoding="utf-8") as f:
        f.write("=== SRS ===\n")
        f.write(extract_text("HamroLabs_SRS_v1.0 - for merge.pdf", keywords))
except Exception as e:
    print(f"Error reading SRS: {e}")

print("Extraction complete.")
