# แนวทางทำแอปแบบ Native (Flutter / React Native)

เอกสารเสริมจาก `MOBILE_APP_PLAN.md` — สำหรับกรณีที่ทีมมีพื้นฐาน Flutter หรือ
React Native อยู่แล้ว และต้องการเลือกทางเลือก C (เขียนแอป native คุยกับ Laravel ผ่าน API)

---

## 1. การมีพื้นฐานอยู่แล้วเปลี่ยนอะไรบ้าง

| | ไม่มีพื้นฐาน | มีพื้นฐานแล้ว |
|---|---|---|
| เฟส 0 — เปิด API (Laravel) | ~2 สัปดาห์ | **~2 สัปดาห์ (ไม่ลดลง)** |
| เฟส 1 — แอปผู้ปกครอง | 8–10 สัปดาห์ | ~4–5 สัปดาห์ |
| ขึ้น Store ครั้งแรก | 2–3 สัปดาห์ | 1–2 สัปดาห์ |
| **รวม** | 3–4 เดือน | **~7–9 สัปดาห์** |

### ข้อสำคัญ
งานฝั่ง Laravel **ไม่ลดลงเลย** เพราะเป็นงานคนละภาษา ตอนนี้ระบบยังไม่มี API
สักตัวเดียว (ไม่มี `routes/api.php`, ไม่ได้ติดตั้ง Sanctum, logic ฝังอยู่ใน
Controller ที่ `return view()`) ต่อให้เก่ง Flutter แค่ไหน ถ้า API ยังไม่มี
ก็เริ่มเขียนแอปไม่ได้

**ดังนั้นลำดับงานที่ถูกต้องคือ: ทำ API ให้เสร็จและล็อก contract ก่อน
แล้วค่อยเริ่มแอป** ไม่ควรทำคู่ขนานตั้งแต่วันแรก เพราะ API ที่ยังเปลี่ยนรูป
ตลอดจะทำให้ต้องรื้อโค้ดแอปซ้ำ ๆ

---

## 2. Flutter หรือ React Native ดี (สำหรับโปรเจกต์นี้)

### ข้อเท็จจริงจากโค้ดปัจจุบัน
`package.json` ของโปรเจกต์นี้มีแค่ `axios`, `tailwindcss`, `vite`
**ไม่มี React, ไม่มี Vue, ไม่มี Livewire** ส่วน JS ในหน้าเว็บใช้
Alpine.js / SweetAlert2 / Cropper.js โหลดจาก CDN

> ระวัง: **Alpine.js ไม่ใช่ React** ถ้า "พื้นฐาน" ที่มีคือ JavaScript ทั่วไป
> การไป React Native ยังต้องเรียน React (hooks, state, JSX, navigation)
> เพิ่มอีกชั้นหนึ่งอยู่ดี ไม่ได้ต่อยอดจากโค้ดเว็บเดิมโดยตรง

### เปรียบเทียบเฉพาะที่เกี่ยวกับแอปนี้

| ประเด็นของแอปนี้ | Flutter | React Native |
|---|---|---|
| หน้าจอตาราง (ตารางเรียน 7 วัน × 10 คาบ, ตารางเกรด) | เขียน widget เองได้ยืดหยุ่น ผลลัพธ์เหมือนกันทั้ง 2 OS | ต้องระวัง layout ต่างกันระหว่าง iOS/Android |
| ฟอนต์ไทย + สระ/วรรณยุกต์ | ฝังฟอนต์ในแอป เรนเดอร์ด้วย engine ตัวเอง คุมได้แน่นอน | ต้อง link ฟอนต์แยกทีละ platform |
| พิมพ์/สร้าง PDF (ปพ., ตารางเรียน) | package `pdf` + `printing` แข็งแรงมาก | ตัวเลือกน้อยกว่า มักต้องพึ่ง native module |
| ออฟไลน์ (เฟสครู — เช็คชื่อในห้อง) | `drift` / `sqflite` นิ่งและครบ | `watermelondb` / `op-sqlite` ใช้ได้แต่ setup ยุ่งกว่า |
| ภาษาที่ต้องดูแล | Dart อย่างเดียว | TS + npm ecosystem ที่เปลี่ยนบ่อย |
| ถ้าอนาคตอยากได้เว็บ/เดสก์ท็อปด้วย | build เป็น web/Windows ได้จาก code เดิม | ต้องใช้ React Native Web (จำกัดกว่า) |
| แรงงานในไทย | หาได้ | หาได้ (มากกว่านิดหน่อย) |

### สรุปคำแนะนำ

> **ถ้าพื้นฐานที่มีคือ React Native → ใช้ React Native**
> **ถ้าพื้นฐานที่มีคือ Flutter หรือยังไม่ได้เลือก → ใช้ Flutter**

ทั้งสองตัวทำแอปนี้ได้ครบ 100% ไม่มีฟีเจอร์ไหนที่ตัวหนึ่งทำได้อีกตัวทำไม่ได้
ดังนั้น **ความคุ้นเคยของทีมสำคัญกว่าข้อดีทางเทคนิคที่ต่างกันเล็กน้อย**
อย่าเปลี่ยนไปใช้ตัวที่ไม่ถนัดเพราะอ่านรีวิวว่าดีกว่า — โปรเจกต์นี้เป็นระบบ
ที่ต้องดูแลยาว หลายปี คนดูแลได้จริงสำคัญที่สุด

สำหรับโปรเจกต์นี้ **ถ้าเริ่มจากศูนย์ ผมแนะนำ Flutter** เพราะแอปเต็มไปด้วย
ตารางและเอกสารที่ต้องเป๊ะเหมือนกันทั้งสองระบบ และเฟสครูในอนาคตต้องออฟไลน์จริง

---

## 3. ⚠️ กับดักสำคัญของโปรเจกต์นี้: พ.ศ. / ปฏิทินไทย

นี่คือความเสี่ยงอันดับหนึ่งของการทำแอป native สำหรับระบบนี้

### สภาพปัจจุบัน
```
ไฟล์ที่ hardcode (+ 543) :  12 ไฟล์
ที่ประกาศ array ชื่อเดือนไทยซ้ำ :  14 จุด
```
กระจายอยู่ใน `PorPor1Controller`, `PorPor3Controller`, `PorPor7Controller`,
`GradeController`, `AttendanceController`, `HolidayController`,
`LeavePersonnelController`, Excel services และ Blade อีกหลายไฟล์
โดยไม่มี helper กลางเลย

### ถ้าทำแอป native แล้วไม่แก้
จะกลายเป็นไฟล์ที่ 13 และจุดที่ 15 — คราวนี้อยู่คนละภาษา (Dart/TS)
และอยู่ในเครื่องผู้ใช้ที่แก้ตามไม่ได้ทันที

ที่แย่กว่านั้น: locale `th` ของ CLDR ตั้ง default calendar เป็น buddhist
ทำให้ `DateFormat` บาง pattern ใน Flutter (`intl`) คืนค่าเป็น พ.ศ. ให้เอง
แต่ `DateTime.year` ยังเป็น ค.ศ. อยู่ → ถ้าทีมเผลอ `+543` ทับอีกชั้น
จะได้ปี 3111 และจะเป็นบั๊กที่โผล่เฉพาะบางหน้าจอ หาสาเหตุยากมาก

### วิธีแก้ที่แนะนำ

**1. รวมศูนย์ในฝั่ง Laravel ก่อน** สร้าง `app/Support/ThaiDate.php`
```php
namespace App\Support;

use Carbon\CarbonInterface;

class ThaiDate
{
    public const MONTHS = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
        'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน',
        'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

    public const MONTHS_SHORT = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.',
        'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

    public static function beYear(CarbonInterface $d): int
    {
        return $d->year + 543;
    }

    /** 5 กันยายน 2568 */
    public static function full(?CarbonInterface $d): ?string
    {
        return $d ? $d->day . ' ' . self::MONTHS[$d->month] . ' ' . self::beYear($d) : null;
    }

    /** 05/09/2568 */
    public static function short(?CarbonInterface $d): ?string
    {
        return $d ? $d->format('d/m/') . self::beYear($d) : null;
    }
}
```
แล้วค่อย ๆ แทนที่ 12 ไฟล์เดิมให้เรียกตัวนี้

**2. API ต้องส่งกลับ 2 ฟิลด์เสมอ**
```json
{
  "date":    "2025-09-05",          // ISO ค.ศ. — ให้แอปใช้เรียง/คำนวณ
  "date_th": "5 กันยายน 2568"        // ข้อความสำเร็จรูป — ให้แอปเอาไปแสดงตรง ๆ
}
```

**3. กฎเหล็กของแอป: แอปห้ามคำนวณ +543 เอง เด็ดขาด**
ให้เซิร์ฟเวอร์เป็นแหล่งความจริงเดียวของการฟอร์แมตวันที่ไทย
แอปแค่แสดง `date_th` และใช้ `date` สำหรับ logic เท่านั้น

ข้อดีคือถ้าอนาคตต้องแก้รูปแบบวันที่ (เช่น ราชการเปลี่ยนแบบฟอร์ม)
แก้ที่เซิร์ฟเวอร์ที่เดียว แอปเก่าที่ติดตั้งไปแล้วได้ผลลัพธ์ใหม่ทันที
ไม่ต้องรอผู้ใช้อัปเดตแอป

---

## 4. ⚠️ กับดักที่สอง: ข้อมูลนักเรียนรั่วผ่าน API

`app/Models/Student.php` ตอนนี้:
```php
protected $guarded = [];              // ทุกฟิลด์ mass-assignable
protected $hidden  = ['parent_password'];
```

ตาราง `students` มีข้อมูลอ่อนไหวเยอะมาก — เลขบัตรประชาชน, ที่อยู่,
ข้อมูลครอบครัว, ข้อมูลสุขภาพ (มี `StudentHealth`, `StudentFamily`,
`StudentAddress` เป็น relation)

ถ้าเขียน API แบบง่าย ๆ ว่า `return response()->json($student);`
→ ข้อมูลทั้งแถวจะหลุดไปอยู่ในเครื่องผู้ใช้ทันที เพราะ `$hidden` กันแค่
`parent_password` ตัวเดียว

**ต้องทำ:**
* ใช้ API Resource แบบ allow-list เท่านั้น — ระบุทีละฟิลด์ว่าจะส่งอะไรออกไป
* อย่าใช้ `$hidden` เป็นด่านป้องกันหลัก (เป็น deny-list ซึ่งลืมง่าย)
* เปลี่ยน `$guarded = []` เป็น `$fillable` ระบุฟิลด์ชัดเจน
  (ควรทำอยู่แล้วแม้ไม่ทำแอป)
* ใส่ rate limit ที่ `/api/v1/parent/login` — เพราะล็อกอินใช้
  "รหัสนักเรียน + รหัสผ่าน" ซึ่งรหัสนักเรียนเดาง่ายเป็นลำดับ

```php
// app/Http/Resources/ParentStudentResource.php
public function toArray($request): array
{
    return [
        'student_id'   => $this->student_id,
        'student_code' => $this->student_code,
        'full_name'    => trim("{$this->first_name} {$this->last_name}"),
        'level'        => $this->whenLoaded('education', fn () => $this->education?->level?->name),
        // ❌ ไม่ส่ง: id_card_number, ที่อยู่, ข้อมูลครอบครัว, ข้อมูลสุขภาพ
    ];
}
```

---

## 5. โครงสร้างที่แนะนำ

### ฝั่ง Laravel (เหมือนกันไม่ว่าจะเลือก Flutter หรือ RN)

```
app/
  Support/ThaiDate.php              ← ใหม่ รวมศูนย์ พ.ศ.
  Services/Parent/
    ParentGradeService.php          ← ย้าย logic ออกจาก Controller
    ParentTimetableService.php
    ParentAnnouncementService.php
  Http/
    Controllers/
      Parent/ParentPortalController.php   ← เดิม คืน view()  → เรียก Service
      Api/V1/Parent/                      ← ใหม่ คืน JSON    → เรียก Service ตัวเดียวกัน
        AuthController.php
        GradeController.php
        TimetableController.php
        AnnouncementController.php
        DeviceController.php
    Resources/V1/                          ← ใหม่ คุมรูปแบบ JSON
routes/
  api.php                                  ← ใหม่
```

หัวใจคือ **Service ตัวเดียว ใช้ทั้งเว็บและแอป** ถ้าปล่อยให้ logic
แตกเป็นสองชุด เว็บกับแอปจะคำนวณ GPA ไม่ตรงกันภายในไม่กี่เดือน
(ตอนนี้ `ParentPortalController@grades` คำนวณ GPA/หน่วยกิตอยู่ในเมธอดโดยตรง)

### ติดตั้ง Sanctum
```bash
composer require laravel/sanctum
php artisan install:api          # สร้าง routes/api.php + migration ให้เอง
php artisan migrate
```

เพิ่ม trait ใน `App\Models\Student` (ตัวนี้เป็น provider ของ guard `parent`)
```php
use Laravel\Sanctum\HasApiTokens;

class Student extends Model implements Authenticatable
{
    use AuthenticatableTrait, HasApiTokens;
    // ...
}
```

> หมายเหตุ: `Student` ใช้ `$primaryKey = 'student_id'` และ override
> `getAuthPassword()` ให้คืน `parent_password` — Sanctum ใช้ได้ปกติ
> แต่ตอนเขียน login endpoint ต้องเทียบรหัสผ่านผ่าน guard `parent`
> เหมือนที่ `ParentAuthController` ทำอยู่ ไม่ใช่เทียบกับคอลัมน์ `password`

### ตัวอย่าง login endpoint
```php
// app/Http/Controllers/Api/V1/Parent/AuthController.php
public function login(Request $request)
{
    $request->validate([
        'student_code' => 'required|string',
        'password'     => 'required|string',
        'device_name'  => 'required|string',
    ]);

    if (! Auth::guard('parent')->once([
        'student_code' => $request->student_code,
        'password'     => $request->password,
    ])) {
        throw ValidationException::withMessages([
            'student_code' => ['รหัสนักเรียนหรือรหัสผ่านไม่ถูกต้อง'],
        ]);
    }

    $student = Auth::guard('parent')->user();

    return response()->json([
        'token'   => $student->createToken($request->device_name)->plainTextToken,
        'student' => new ParentStudentResource($student),
    ]);
}
```
```php
// routes/api.php
Route::prefix('v1/parent')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');            // กัน brute force

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',            [AuthController::class, 'me']);
        Route::post('logout',       [AuthController::class, 'logout']);
        Route::get('grades',        [GradeController::class, 'index']);
        Route::get('timetable',     [TimetableController::class, 'index']);
        Route::get('announcements', [AnnouncementController::class, 'index']);
        Route::post('devices',      [DeviceController::class, 'store']);
    });
});
```

### ฝั่งแอป — วางไว้ที่ไหน
แนะนำ **แยก repository ต่างหาก** (เช่น `schooltest-mobile`)
เพราะ `.gitignore` ของ repo นี้เขียนมาสำหรับ PHP/Laravel และรอบการปล่อยของ
คนละจังหวะกัน (เว็บ deploy ได้ทุกวัน แอปต้องรอ Store review)

ถ้าอยากอยู่ repo เดียวกันจริง ๆ ให้ใส่ที่ `mobile/` และเพิ่ม `.gitignore` แยกในนั้น

### โครงแอป Flutter ที่แนะนำ
```
lib/
  core/
    api_client.dart          ← dio + interceptor แนบ token + จับ 401
    token_storage.dart       ← flutter_secure_storage
  features/
    auth/
    dashboard/
    grades/
    timetable/
    announcements/
    calendar/
  models/                    ← สร้างจาก JSON ของ API
```
package ที่จะใช้: `dio`, `flutter_secure_storage`, `go_router`,
`riverpod` (หรือ `bloc`), `firebase_messaging`, `local_auth`, `intl`

---

## 6. ขอบเขตที่ควรทำในแอป native (อย่าลอกเว็บ 1:1)

| หน้าจอ | ทำในแอป | หมายเหตุ |
|---|---|---|
| เข้าสู่ระบบ | ✅ | ครั้งแรกใส่รหัส ครั้งต่อไปใช้ลายนิ้วมือ/Face ID |
| หน้าหลัก | ✅ | ประกาศใหม่ + คาบเรียนวันนี้ + เกรดล่าสุด |
| ประกาศ + กดรับทราบ | ✅ | จุดขายหลัก — ผูก Push Notification |
| ผลการเรียน | ✅ | แยกตามภาคเรียน แคชไว้ดูออฟไลน์ |
| ตารางเรียน | ✅ | widget ที่ยากที่สุด เผื่อเวลาไว้เยอะ ๆ |
| ปฏิทิน/วันหยุด | ✅ | |
| ติดต่อครูประจำชั้น | ✅ | ปุ่มโทร/แชทตรงจากแอป |
| เปลี่ยนรหัสผ่าน | ✅ | |
| **พิมพ์ตารางเรียน** (`timetable_print`) | ❌ | ให้เซิร์ฟเวอร์สร้าง PDF แล้วแอปแค่เปิด/แชร์ |
| **ปพ. ทุกตัว, Excel, จัดการหลักสูตร** | ❌ | อยู่บนเว็บต่อไป |

หน้าที่ต้อง "พิมพ์" ทั้งหมด อย่าสร้าง layout ซ้ำในแอป — ให้ทำ endpoint
คืน PDF จากฝั่ง Laravel (ซึ่งมี logic การจัดหน้าอยู่แล้ว) แล้วแอปเปิดด้วย
PDF viewer หรือส่งต่อให้ระบบแชร์ของเครื่อง ประหยัดงานไปมากและได้เอกสาร
ที่หน้าตาตรงกับเว็บเป๊ะ

---

## 7. สิ่งที่ต้องเตรียมเพิ่มเมื่อเลือกทาง native

* **ล็อก API contract ก่อนเริ่มเขียนแอป** — เขียน Feature test ครอบทุก
  endpoint ให้ครบก่อน ถือ test เป็นสัญญา ถ้า test พัง = ทำแอปที่ปล่อยไปแล้วพัง
* **บังคับอัปเดตเวอร์ชัน** — ต้องมี `GET /api/v1/app/version` คืน
  `min_supported` เพราะผู้ใช้จำนวนมากไม่ยอมอัปเดตแอป และ API เก่าจะลบไม่ได้เลย
* **queue worker จริง** — Push notification ต้องยิงผ่าน queue
  ตอนนี้ `.env.example` ตั้ง `QUEUE_CONNECTION=database` แต่ยังไม่มี worker
* **ย้ายจาก SQLite เป็น MySQL/PostgreSQL** ก่อนมีแอปยิง API พร้อมกันหลายร้อยเครื่อง
* **เครื่อง Mac + Xcode** สำหรับ build iOS (หรือ Codemagic / GitHub Actions macOS runner)
* **CI สำหรับแอป** — build .apk/.ipa อัตโนมัติ ส่งเข้า TestFlight / Play Internal Testing

---

## 8. สรุป

1. มีพื้นฐานอยู่แล้ว → ทาง native (C) **เป็นไปได้จริง** เหลือ ~7–9 สัปดาห์
   แทนที่จะเป็น 3–4 เดือน
2. **แต่งานฝั่ง Laravel ~2 สัปดาห์แรกไม่หายไปไหน** ต้องเปิด API + แยก Service
   + รวมศูนย์ พ.ศ. + ทำ API Resource ให้เสร็จก่อน
3. **Flutter vs React Native — เลือกตัวที่ทีมถนัด** ทำได้ทั้งคู่
   ถ้ายังไม่ได้เลือก แนะนำ Flutter สำหรับโปรเจกต์นี้
4. กับดักใหญ่สุดคือ **พ.ศ. ที่ hardcode อยู่ 12 ไฟล์** และ
   **ข้อมูลนักเรียนที่อาจรั่วผ่าน API** — ต้องจัดการก่อนเริ่มเขียนแอป
5. ถ้าอยากได้ของเร็วกว่านี้และงบน้อยกว่า ทางเลือก B (Capacitor) ยังคุ้มกว่า —
   ดู `MOBILE_APP_PLAN.md` ประกอบ
