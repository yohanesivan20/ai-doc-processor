# 🚀 AI Document Processor (Laravel + OCR + AI)

AI Document Processor adalah aplikasi backend berbasis Laravel yang mampu:

* 📄 Mengupload dokumen (PDF / Image)
* 🔍 Melakukan OCR (Optical Character Recognition)
* 🤖 Mengekstrak data terstruktur menggunakan AI (LLM via Ollama)
* ⚙️ Memproses data secara asynchronous menggunakan Queue
* 📦 Menghasilkan output JSON siap pakai (invoice parsing)

---

# 🧠 Features

* ✅ Upload dokumen (PDF / JPG / PNG)
* ✅ OCR menggunakan Tesseract
* ✅ PDF → Image conversion menggunakan Poppler
* ✅ AI Extraction (Invoice parsing)
* ✅ Queue system (background processing)
* ✅ Logging & error handling
* ✅ REST API ready

---

# 🏗️ Tech Stack

* **Backend**: Laravel 10 (PHP)
* **Database**: MySQL
* **OCR Engine**: Tesseract OCR
* **PDF Converter**: Poppler (pdftoppm)
* **AI Engine**: Ollama (Mistral / LLaMA / lainnya)
* **Queue**: Laravel Queue (Database / Redis)
* **HTTP Client**: Laravel HTTP Client

---

# 🔄 System Flow

```text
Upload Document
     ↓
PDF → Image (Poppler)
     ↓
OCR (Tesseract)
     ↓
Text Cleaning
     ↓
AI Extraction (Ollama)
     ↓
Structured JSON Result
     ↓
Stored in Database
```

---

# 📦 Installation & Setup

## 1. Clone Repository

```bash
git clone https://github.com/your-username/ai-doc-processor.git
cd ai-doc-processor
```

---

## 2. Install Dependencies

```bash
composer install
```

---

## 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

---

## 4. Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_doc
DB_USERNAME=root
DB_PASSWORD=
```

---

## 5. Run Migration

```bash
php artisan migrate
```

---

# ⚙️ Install Dependencies (WAJIB)

## 🔥 1. Install Tesseract OCR

Download:
https://github.com/tesseract-ocr/tesseract

Set path di `.env`:

```env
TESSERACT_PATH="C:/Program Files/Tesseract-OCR/tesseract.exe"
```

Test:

```bash
tesseract -v
```

---

## 🔥 2. Install Poppler (PDF → Image)

### Windows (Chocolatey)

```bash
choco install poppler
```

Test:

```bash
pdftoppm -h
```

---

## 🔥 3. Install Ollama

Download:
https://ollama.com

Jalankan:

```bash
ollama run mistral
```

Atau:

```bash
ollama serve
```

---

## 🔧 Setup di `.env`

```env
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=mistral
OLLAMA_TIMEOUT=60
```

---

# 🚀 Running the Project

## 1. Jalankan Laravel

```bash
php artisan serve
```

---

## 2. Jalankan Queue Worker

```bash
php artisan queue:work
```

---

## 3. Jalankan Ollama

```bash
ollama serve
```

---

# 📡 API Usage

## Upload Document

```http
POST /api/documents
```

### Request:

* file (Image : JPEG, JPG, PNG)

---

## Response:

```json
{
  "id": 1,
  "status": "pending"
}
```

---

## Setelah diproses:

```json
{
  "invoice_number": "INV-2026-001",
  "date": "2026-04-20",
  "vendor": "PT Sumber Rejeki",
  "customer": "PT Maju Mundur",
  "total": 6600000
}
```

---

# 🧪 Sample Input (Invoice)

```
PT Maju Mundur
Vendor: PT Sumber Rejeki
Web Development Service Rp 5,000,000
Hosting Rp 1,000,000
Total: Rp 6,600,000
```

---

# ⚠️ Known Limitations

* OCR akurasi tergantung kualitas dokumen
* AI bisa menghasilkan JSON tidak valid (sudah di-handle parser)
* Ollama membutuhkan resource cukup besar (RAM/CPU)
* Belum support multi-page parsing secara penuh

---

# 🔮 Future Improvements

* 📊 Dashboard UI
* 📈 Confidence score extraction
* 📑 Multi-page PDF support
* 📦 Export ke Excel / Google Sheets
* 🔐 Authentication system
* 🌐 Deploy full SaaS

---

# 🚀 Deployment

Project ini bisa di-deploy menggunakan:

* Docker
* Render (Free Tier)
* VPS (untuk Ollama)

---

# 🧠 Why This Project?

Project ini dibuat untuk:

* Menunjukkan kemampuan backend engineering
* Mengintegrasikan OCR + AI
* Membangun real-world document processing system
* Demonstrasi penggunaan queue & async processing

---

# 👨‍💻 Author

Developed by [Your Name]

---

# ⭐ Support

Jika project ini membantu, silakan beri ⭐ di repository ini!
