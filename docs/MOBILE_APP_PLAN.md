# แผนทำแอป Android / iOS จากระบบ Laravel เดิม

> **เอกสารชุดนี้**
> * `MOBILE_APP_PLAN.md` (ไฟล์นี้) — ภาพรวมและเปรียบเทียบทางเลือก
> * `MOBILE_APP_CAPACITOR.md` — เจาะลึกทางเลือก B และ 2 โหมดของ Capacitor
> * `MOBILE_APP_NATIVE.md` — เจาะลึกทางเลือก C (Flutter / React Native)
> * `MOBILE_APP_IOS.md` — **ถ้าต้องมี iOS ด้วย อ่านไฟล์นี้** (ข้อสรุป: ใช้ Flutter/RN)
> * `MOBILE_APP_TIMELINE.md` — **รายการงาน + ชั่วโมง + ไทม์ไลน์ 3 แบบ (เร็วสุด/ชิว ๆ/ครบสุด)**

เอกสารนี้สรุปแนวทางแนะนำสำหรับการต่อยอดระบบ `schooltest` (Laravel 12 + Blade)
ให้กลายเป็นแอปพลิเคชันบนมือถือ Android และ iOS

---

## 1. สรุปสภาพระบบปัจจุบัน (ข้อมูลจริงจากโค้ด)

| หัวข้อ | สภาพปัจจุบัน |
|---|---|
| Framework | Laravel 12 / PHP 8.2 |
| หน้าเว็บ | Blade 137 ไฟล์ (Server-side rendering ล้วน) |
| CSS | Bootstrap 5 (CDN) ในฝั่งผู้ปกครอง, Tailwind 4 + Vite ในบางส่วน |
| Auth | Session guard 2 ตัว — `web` (บุคลากร) และ `parent` (ผู้ปกครอง/นักเรียน) |
| API | **ยังไม่มี** — ไม่มี `routes/api.php`, ไม่ได้ติดตั้ง Sanctum |
| Database | 57 migrations, ตั้งค่า default เป็น SQLite |
| ฟีเจอร์หลัก | นักเรียน, บุคลากร, วิชา/หลักสูตร, ตารางสอน, คะแนน/เกรด, ปพ.1/2/3/5/6/7, ใบลา, ห้องสมุด, รับสมัครออนไลน์, ประกาศ, ปฏิทินวันหยุด |
| ส่วนผู้ปกครอง | `/parent/*` มี 8 หน้า: dashboard, ประกาศ, ผลการเรียน, ตารางเรียน, ปฏิทิน, ติดต่อครู, เปลี่ยนรหัสผ่าน |

### ข้อสรุปสำคัญ
ระบบฝั่ง **หลังบ้าน (บุคลากร) ไม่เหมาะจะทำเป็นแอปมือถือ** เพราะเป็นงานตาราง
กว้าง ๆ งานพิมพ์เอกสารราชการ (ปพ.) และงานนำเข้า Excel ซึ่งทำบนคอมพิวเตอร์
สะดวกกว่ามาก

ส่วนที่ **คุ้มค่าที่สุดที่จะทำเป็นแอป** คือ `/parent/*` ซึ่งมีอยู่แล้ว
เป็นหน้าจออ่านข้อมูลเป็นหลัก จอเล็กแสดงผลได้ดี และเป็นกลุ่มผู้ใช้ที่ใหญ่ที่สุด
(ผู้ปกครองทุกคน) — ตรงกับนิยามของ "แอปโรงเรียน" ที่ลูกค้ามักต้องการ

---

## 2. ทางเลือกในการพัฒนา (เปรียบเทียบ)

### ทางเลือก A — PWA (Progressive Web App)
ทำเว็บเดิมให้ติดตั้งลงหน้าจอโฮมได้ ใช้งานออฟไลน์บางส่วนได้

* **เขียนด้วย**: โค้ดเดิม + `manifest.json` + Service Worker (JavaScript)
* **แรงที่ใช้**: ~1–2 สัปดาห์
* **ค่าใช้จ่าย**: 0 บาท ไม่ต้องขึ้น Store ไม่ต้องรอรีวิว
* **ข้อดี**: เร็วที่สุด, อัปเดตทันทีไม่ต้องรอรีวิว, ใช้โค้ด Blade เดิมทั้งหมด
* **ข้อเสีย**: ไม่มีไอคอนใน Store (ผู้ปกครองต้องกด "เพิ่มลงหน้าจอโฮม" เอง),
  Push Notification บน iOS ทำได้เฉพาะเมื่อผู้ใช้ Add to Home Screen แล้ว (iOS 16.4+)

### ทางเลือก B — Capacitor (ห่อเว็บเป็นแอปจริง)

> ⚠️ **อ่าน `MOBILE_APP_CAPACITOR.md` ประกอบด้วย** — Capacitor มี 2 โหมด
> (Remote URL กับ Bundled assets) ที่ใช้แรงต่างกันมหาศาล หัวข้อนี้สรุปแบบรวม ๆ
> ซึ่งอาจทำให้ประเมินงานต่ำเกินจริง
ใช้ Ionic Capacitor สร้าง native shell ครอบเว็บ Laravel เดิม
ได้ไฟล์ `.apk` / `.ipa` ขึ้น Store ได้จริง

* **เขียนด้วย**: JavaScript/TypeScript (Capacitor) + Laravel เดิม ไม่ต้องเขียน UI ใหม่
* **แรงที่ใช้**: ~3–5 สัปดาห์ (รวมปรับ UI ให้เป็น mobile-first + ทำ Push)
* **ค่าใช้จ่าย**: Apple Developer 99 USD/ปี, Google Play 25 USD ครั้งเดียว
* **ข้อดี**: ได้แอปจริงใน App Store / Play Store, ใช้ Push Notification (FCM),
  กล้อง, ไฟล์, สแกน QR, ล็อกอินด้วยลายนิ้วมือ/Face ID ได้, ทีมเดิมดูแลต่อได้ (ไม่ต้องเรียนภาษาใหม่)
* **ข้อเสีย**: ความลื่นสู้ native ไม่ได้ 100%, ต้องระวัง Apple Guideline 4.2
  (ถ้าเป็นแค่เว็บครอบเฉย ๆ จะโดนปฏิเสธ — ต้องมีฟีเจอร์ native จริงอย่างน้อย Push + Offline)

### ทางเลือก C — Flutter หรือ React Native (Native cross-platform)
เขียนแอปใหม่ทั้งหมด คุยกับ Laravel ผ่าน REST API

* **เขียนด้วย**: Flutter (ภาษา Dart) หรือ React Native (TypeScript) + Laravel API
* **แรงที่ใช้**: ~2–3 เดือน สำหรับส่วนผู้ปกครอง
* **ค่าใช้จ่าย**: ค่า Store เท่ากับข้อ B + ค่าพัฒนาที่สูงกว่ามาก
* **ข้อดี**: UX ดีที่สุด, ทำงานออฟไลน์เต็มรูปแบบ, เหมาะกับฟีเจอร์ที่ครูต้องใช้ภาคสนาม
  (เช็คชื่อในห้อง, เยี่ยมบ้าน, ถ่ายรูป)
* **ข้อเสีย**: ต้องมีคนดูแลอีกภาษาหนึ่ง, ทุกฟีเจอร์ต้องเขียน 2 รอบ (API + UI)

### ตารางสรุปการตัดสินใจ

| ถ้าลูกค้าต้องการ... | ให้เลือก |
|---|---|
| "แค่เปิดในมือถือให้สะดวก" งบน้อย เอาเร็ว | **A — PWA** |
| "อยากมีแอปใน Play Store เร็ว ๆ" (Android เป็นหลัก) | **A + B โหมด Remote URL** ⭐ |
| **"ต้องมี iOS ด้วย"** | **C — Flutter / RN** ⭐ (ดู `MOBILE_APP_IOS.md`) |
| "ครูต้องเช็คชื่อ/กรอกคะแนนออฟไลน์ในห้องเรียน" | **C — Flutter** (เฉพาะโมดูลครู) |

> หมายเหตุ: ถ้าทีมมีพื้นฐาน Flutter หรือ React Native อยู่แล้ว
> ข้อได้เปรียบของ Capacitor จะลดลงมาก เพราะงานหนักจริง ๆ คือการเปิด API
> ฝั่ง Laravel ซึ่งต้องทำเหมือนกันทั้งคู่ — ดู `MOBILE_APP_CAPACITOR.md` ข้อ 1

---

## 3. แนวทางที่แนะนำ: ทำเป็น 3 เฟส

### เฟส 0 — เตรียมฐาน API (จำเป็นทุกทางเลือก) ~1–2 สัปดาห์

ตอนนี้ระบบยังไม่มี API เลย ทุกอย่างเป็น Blade + Session
งานนี้ต้องทำก่อน ไม่ว่าจะเลือกทาง B หรือ C

1. **ติดตั้ง Laravel Sanctum** สำหรับ token auth (session cookie ใช้กับแอปได้ไม่ดี)
   ```bash
   composer require laravel/sanctum
   php artisan install:api
   ```

2. **เพิ่ม `routes/api.php`** และ trait `HasApiTokens` ใน `App\Models\Student`
   (ที่ใช้เป็น provider ของ guard `parent`)

3. **ย้าย logic ออกจาก Controller ไปเป็น Service**
   ตอนนี้ `ParentPortalController` มี query ฝังอยู่ในเมธอดโดยตรง เช่น
   `grades()` คำนวณ GPA/หน่วยกิตในตัวเมธอดเลย ถ้าไม่แยกออกมาจะต้องเขียนซ้ำ
   ในฝั่ง API

   ```
   app/Services/Parent/ParentGradeService.php
   app/Services/Parent/ParentTimetableService.php
   app/Services/Parent/ParentAnnouncementService.php
   ```
   แล้วให้ทั้ง `ParentPortalController` (คืน view) และ
   `Api\ParentController` (คืน JSON) เรียก Service ตัวเดียวกัน

4. **ทำ API Resource** ใน `app/Http/Resources/` เพื่อคุมรูปแบบ JSON ให้คงที่
   (สำคัญมาก เพราะแอปที่ผู้ใช้ติดตั้งไปแล้วจะแก้ตามไม่ได้ทันที)

5. **ทำ API versioning** — `/api/v1/...` ตั้งแต่วันแรก

#### รายการ endpoint ขั้นต่ำสำหรับแอปผู้ปกครอง

| Method | Endpoint | มาจากเมธอดเดิม |
|---|---|---|
| POST | `/api/v1/parent/login` | `ParentAuthController@login` |
| POST | `/api/v1/parent/logout` | `ParentAuthController@logout` |
| GET | `/api/v1/parent/me` | ข้อมูลนักเรียน + ห้องเรียนปัจจุบัน |
| GET | `/api/v1/parent/dashboard` | `ParentPortalController@dashboard` |
| GET | `/api/v1/parent/grades?semester_id=` | `@grades` |
| GET | `/api/v1/parent/timetable` | `@timetable` |
| GET | `/api/v1/parent/calendar?month=` | `@calendar` |
| GET | `/api/v1/parent/announcements` | `@announcements` |
| GET | `/api/v1/parent/announcements/{id}` | `@announcementShow` |
| POST | `/api/v1/parent/announcements/{id}/acknowledge` | `@announcementAcknowledge` |
| POST | `/api/v1/parent/change-password` | `@changePassword` |
| POST | `/api/v1/parent/devices` | ลงทะเบียน FCM token (ของใหม่) |
| GET | `/api/v1/app/version` | บังคับอัปเดตเวอร์ชัน (ของใหม่) |

### เฟส 1 — ปล่อย PWA ก่อน ~1–2 สัปดาห์

ปล่อยของให้ลูกค้าได้ใช้เร็ว ๆ ระหว่างรอแอปจริง

* เพิ่ม `public/manifest.json` (ชื่อแอป, ไอคอน, theme color, `display: standalone`)
* เพิ่ม Service Worker แคชหน้า/รูป (แนะนำ Workbox)
* **ย้าย Bootstrap + Bootstrap Icons + Google Fonts จาก CDN มาไว้ในเครื่อง**
  — ตอนนี้ `resources/views/parent/layout.blade.php` โหลดจาก CDN ทั้งหมด
  ถ้าไม่ย้าย จะแคชออฟไลน์ไม่ได้และหน้าจะพังเวลาเน็ตช้า
* ปรับ `parent/layout.blade.php` ให้เป็น mobile-first:
  เปลี่ยนเมนูแนวนอน 7 อันเป็น bottom tab bar 4–5 อัน
* เพิ่มปุ่ม "ติดตั้งแอป" ที่หน้า `/parent/login`

### เฟส 2 — ห่อด้วย Capacitor ขึ้น Store ~3–4 สัปดาห์

```bash
npm install @capacitor/core @capacitor/cli
npx cap init "โรงเรียน..." "th.ac.school.app"
npx cap add android
npx cap add ios
npm install @capacitor/push-notifications @capacitor/preferences \
            @capacitor/filesystem @capacitor/browser
```

สิ่งที่ต้องทำเพิ่ม เพื่อไม่ให้ Apple ปฏิเสธ (Guideline 4.2 Minimum Functionality):

1. **Push Notification จริง** — ผูก FCM แจ้งเตือนเมื่อมีประกาศใหม่จากตาราง
   `announcements` / `announcement_recipients` (มีอยู่แล้วในระบบ)
   ฝั่ง Laravel ใช้ Notification channel ยิงเข้า FCM ผ่าน queue
2. **เก็บ token ไว้ในเครื่อง** ด้วย `@capacitor/preferences` ไม่ต้องล็อกอินซ้ำ
3. **ล็อกอินด้วยลายนิ้วมือ / Face ID** (ปลั๊กอิน biometric)
4. **แคชผลการเรียน/ตารางเรียนไว้ดูออฟไลน์**
5. **บันทึกไฟล์แนบประกาศลงเครื่อง** ผ่าน `@capacitor/filesystem`

### เฟส 3 (ทำต่อเมื่อจำเป็น) — แอปสำหรับครู

ถ้าลูกค้าอยากได้ฝั่งครูด้วย **อย่ายกมาทั้งระบบ** ให้เลือกเฉพาะงานที่ต้องทำนอกโต๊ะ:

* เช็คชื่อนักเรียนหน้าห้อง (ต้องออฟไลน์ได้จริง → เหมาะกับ Flutter)
* บันทึกพฤติกรรม (`behavior_records` มีตารางแล้ว)
* บันทึกเยี่ยมบ้าน + ถ่ายรูป + GPS (`home_visits` มีตารางแล้ว)
* อนุมัติใบลา (`leave_requests`)
* ดูตารางสอนของตัวเอง

งานพิมพ์ ปพ. / นำเข้า Excel / จัดการหลักสูตร **ให้อยู่บนเว็บต่อไป**

---

## 4. เรื่องที่ต้องเตรียมนอกเหนือจากโค้ด

### บัญชีและค่าใช้จ่าย
| รายการ | ราคา | หมายเหตุ |
|---|---|---|
| Apple Developer Program | 99 USD/ปี | ถ้าจดในนามโรงเรียน/บริษัท ต้องมีเลข D-U-N-S (ขอฟรี ใช้เวลา 1–2 สัปดาห์) |
| Google Play Console | 25 USD ครั้งเดียว | บัญชีองค์กรต้องยืนยันตัวตน |
| เครื่อง Mac | จำเป็น | ต้องใช้ Xcode build iOS (หรือใช้บริการ CI เช่น Codemagic / GitHub Actions macOS runner) |
| Firebase (FCM) | ฟรี | สำหรับ Push Notification |

### ความปลอดภัย / กฎหมาย
* **PDPA** — แอปเก็บข้อมูลนักเรียน ต้องมีหน้านโยบายความเป็นส่วนตัว
  (Apple/Google บังคับต้องมี URL นโยบายก่อนส่งขึ้น Store อยู่แล้ว)
* บังคับ HTTPS ทั้งหมด และเปิด certificate pinning ถ้าทำได้
* ตั้ง token ให้หมดอายุ + มีปุ่ม revoke อุปกรณ์ทั้งหมดเมื่อทำโทรศัพท์หาย
* ปัจจุบันล็อกอินฝั่งผู้ปกครองใช้ "รหัสนักเรียน + รหัสผ่าน" — ควรเพิ่ม
  rate limit บน endpoint API และบังคับเปลี่ยนรหัสผ่านครั้งแรก

### โครงสร้างพื้นฐาน
* ตอนนี้ `.env.example` ตั้ง `DB_CONNECTION=sqlite`, `QUEUE_CONNECTION=database`,
  `MAIL_MAILER=log` — ก่อนขึ้น production ที่มีแอปยิง API ควรย้ายไป
  **MySQL/PostgreSQL** และมี **queue worker จริง** (Push notification ต้องยิงผ่าน queue)
* ควรมี staging server แยก เพราะแอปที่ปล่อยไปแล้วย้อนกลับไม่ได้ทันที

---

## 5. สรุปคำแนะนำสั้น ๆ สำหรับตอบลูกค้า

> ระบบตอนนี้เป็นเว็บ Laravel ที่ครบแล้ว การทำแอปไม่ต้องเขียนใหม่ทั้งหมดครับ
> แนะนำให้ทำ **เฉพาะส่วนผู้ปกครอง** เป็นแอปก่อน โดย
>
> 1. เปิด API (Laravel Sanctum) ให้ระบบเดิม — 1–2 สัปดาห์
> 2. ปล่อย PWA ให้ใช้งานได้ทันทีระหว่างรอ — 1–2 สัปดาห์
> 3. ห่อด้วย Capacitor ขึ้น Play Store / App Store พร้อมระบบแจ้งเตือนประกาศ — 3–4 สัปดาห์
>
> รวมประมาณ **6–8 สัปดาห์** ได้แอปจริงทั้ง Android และ iOS
> โดยไม่ต้องทิ้งโค้ดเว็บเดิม และดูแลต่อด้วยทีมเดิมได้
>
> ส่วนฝั่งครู/ธุรการ (พิมพ์ ปพ., นำเข้า Excel, จัดการหลักสูตร) แนะนำให้ใช้บนเว็บต่อไป
> ถ้าอยากได้แอปครูด้วย ค่อยทำเฉพาะ "เช็คชื่อ / บันทึกพฤติกรรม / เยี่ยมบ้าน / อนุมัติใบลา"
> เป็นเฟสถัดไป

---

## 6. งานถัดไปที่ทำได้ทันที (ถ้าตกลงแนวทางนี้)

- [ ] ติดตั้ง Sanctum + สร้าง `routes/api.php`
- [ ] แยก logic ของ `ParentPortalController` ออกเป็น Service
- [ ] สร้าง API Resource + endpoint ตามตารางในหัวข้อ 3
- [ ] เขียน Feature test ครอบ endpoint ทุกตัว
- [ ] ย้าย Bootstrap/Fonts จาก CDN มาไว้ในโปรเจกต์
- [ ] ปรับ `parent/layout.blade.php` เป็น mobile-first + bottom nav
- [ ] เพิ่ม `manifest.json` + Service Worker
- [ ] สร้างตาราง `device_tokens` + endpoint ลงทะเบียนอุปกรณ์
- [ ] ต่อ FCM + Notification เมื่อมีประกาศใหม่
