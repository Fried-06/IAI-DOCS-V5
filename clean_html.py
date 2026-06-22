import os
import glob

directory = r"C:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\subjects"

html_files = glob.glob(os.path.join(directory, "**", "*.html"), recursive=True)

count = 0
for file_path in html_files:
    os.remove(file_path)
    count += 1
    print(f"Deleted: {file_path}")

print(f"Total deleted: {count}")
