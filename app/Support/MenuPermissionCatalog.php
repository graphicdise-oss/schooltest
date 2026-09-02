<?php

namespace App\Support;

/**
 * รายการเมนูทั้งหมดที่กำหนดสิทธิ์การเข้าถึงได้ — ใช้ร่วมกันระหว่างหน้า "กำหนดสิทธิ์" ของตำแหน่ง
 * (PositionController) และของประเภทบุคลากร (PersonnelTypeController — เดิมใช้งานจริง ตอนนี้ย้ายมาที่
 * ตำแหน่งแล้ว โค้ดยังเก็บไว้เผื่ออ้างอิง ไม่ได้ผูกกับการเช็คสิทธิ์จริงอีกต่อไป)
 *
 * รายการนี้คัดลอกมาจากเมนูจริงในไซด์บาร์ (resources/views/layouts/sidebar.blade.php) ทุกรายการ —
 * ไม่มีเมนูสมมติ/ยังไม่เปิดใช้งานปนอยู่ ถ้าเพิ่มเมนูใหม่ในไซด์บาร์ ต้องมาเพิ่มที่นี่ด้วยให้ตรงกัน
 */
class MenuPermissionCatalog
{
    public static function list(): array
    {
        return [
            'ข้อมูลบุคคล' => [
                ['key' => 'personnel.students.index', 'label' => 'ข้อมูลนักเรียน'],
                ['key' => 'personnel.student-types.index', 'label' => 'จำแนกประเภทนักเรียน'],
                ['key' => 'personnel.promotions.index', 'label' => 'จัดการชั้นเรียน/สำเร็จการศึกษา'],
                ['key' => 'personnel.open-semester2', 'label' => 'เปิดภาคเรียน'],
                ['key' => 'personnel.student-alumni.index', 'label' => 'ทำเนียบศิษย์เก่า'],
                ['key' => 'personnel.student-alumni.import', 'label' => 'นำเข้าทำเนียบศิษย์เก่า'],
                ['key' => 'personnel.class-roster.index', 'label' => 'บัญชีรายชื่อนักเรียน'],
                ['key' => 'personnel.student-stat.index', 'label' => 'สรุปสถิตินักเรียน'],
                ['key' => 'personnel.student-alumni.withdrawal', 'label' => 'รายงานนักเรียนพ้นสภาพ'],
                ['key' => 'personnel.personnels.index', 'label' => 'ข้อมูลบุคลากร/ครู'],
                ['key' => 'personnel.leave.personnel.index', 'label' => 'บันทึกการลางาน'],
                ['key' => 'personnel.reports.staff-list', 'label' => 'บัญชีรายชื่อบุคลากร'],
                ['key' => 'personnel.reports.leave-summary', 'label' => 'สรุปรายงานการลา'],
                ['key' => 'personnel.reports.training', 'label' => 'สรุปการพัฒนา/อบรมบุคลากร'],
                ['key' => 'personnel.settings.prefixes', 'label' => 'จัดการคำนำหน้าชื่อ'],
                ['key' => 'personnel.settings.personnel-types', 'label' => 'จัดการประเภทบุคลากร'],
                ['key' => 'personnel.settings.positions', 'label' => 'จัดการตำแหน่งงาน'],
                ['key' => 'personnel.settings.departments', 'label' => 'จัดการฝ่าย/แผนกงาน'],
                ['key' => 'personnel.settings.leave-types', 'label' => 'จัดการประเภทการลา'],
                ['key' => 'personnel.settings.leave-settings', 'label' => 'จัดการสิทธิ์วันลา'],
                ['key' => 'personnel.settings.holidays', 'label' => 'จัดการปฏิทินวันหยุด'],
                ['key' => 'personnel.student-cards.index', 'label' => 'ออกบัตรประจำตัวนักเรียน'],
            ],
            'วิชาการ' => [
                ['key' => 'academic.subjects.index', 'label' => 'จัดการรายวิชา'],
                ['key' => 'academic.programs.index', 'label' => 'จัดการหลักสูตร'],
                ['key' => 'academic.class-sections.index', 'label' => 'จัดการห้องเรียน'],
                ['key' => 'academic.timetable.view', 'label' => 'ดูตารางสอน'],
                ['key' => 'academic.timetable.index', 'label' => 'จัดการตารางสอน'],
                ['key' => 'academic.reports.avg-score', 'label' => 'รายงานคะแนนเฉลี่ย 2 ภาคเรียน'],
                ['key' => 'academic.reports.subject-rank', 'label' => 'รายงานจัดอันดับคะแนนรายวิชา'],
                ['key' => 'academic.por1', 'label' => 'ระเบียนแสดงผลการเรียน (ปพ.1)'],
                ['key' => 'academic.pp2', 'label' => 'ใบประกาศนียบัตร (ปพ.2)'],
                ['key' => 'academic.por3', 'label' => 'แบบรายงานผู้สำเร็จการศึกษา (ปพ.3)'],
                ['key' => 'academic.por5', 'label' => 'แบบบันทึกผลการพัฒนาคุณภาพผู้เรียน (ปพ.5)'],
                ['key' => 'academic.por6', 'label' => 'แบบรายงานผลพัฒนาคุณภาพผู้เรียนรายบุคคล (ปพ.6)'],
                ['key' => 'academic.por7', 'label' => 'ใบรับรองผลการเรียน (ปพ.7)'],
                ['key' => 'academic.scores.index', 'label' => 'บันทึกคะแนนผลการเรียน'],
                ['key' => 'academic.grades.index', 'label' => 'แก้ไขเกรด'],
                ['key' => 'academic.assessments.index', 'label' => 'บันทึกผลการประเมิน (อ่าน/คุณลักษณะ/กิจกรรม)'],
                ['key' => 'academic.grades.import', 'label' => 'นำเข้าเกรดสำหรับปพ.1/รบ.1'],
                ['key' => 'academic.onet.index', 'label' => 'นำเข้า ONET'],
            ],
            'กิจกรรมพัฒนาผู้เรียน' => [
                ['key' => 'affairs.attendance.index', 'label' => 'ปรับสถานะการมาเรียน'],
                ['key' => 'affairs.behavior-items.index', 'label' => 'เพิ่มข้อมูล/ตั้งค่าการตัดคะแนนความประพฤติ'],
                ['key' => 'affairs.behavior-scores.index', 'label' => 'ตัดคะแนนความประพฤติ'],
                ['key' => 'affairs.home-visits.status', 'label' => 'บันทึกสถานะการเยี่ยมบ้าน'],
                ['key' => 'affairs.home-visits.results', 'label' => 'สรุปผลการเยี่ยมบ้าน'],
                ['key' => 'affairs.pickup-notices.index', 'label' => 'การรับ-ส่งนักเรียน (ที่ผู้ปกครองแจ้ง)'],
            ],
            'บริหารทั่วไป' => [
                ['key' => 'admin.announcements.index', 'label' => 'ประชาสัมพันธ์/รายงานประชาสัมพันธ์'],
                ['key' => 'admin.admission.form', 'label' => 'หน้ารับสมัคร (สาธารณะ)'],
                ['key' => 'admin.admissions.applicants', 'label' => 'ตรวจสอบผู้สมัคร'],
                ['key' => 'admin.admissions.settings', 'label' => 'ตั้งค่าระบบรับนักเรียน'],
                ['key' => 'admin.exam-rooms.index', 'label' => 'ตั้งค่าห้องสอบ'],
                ['key' => 'admin.library.checkin', 'label' => 'ลงชื่อเข้าใช้ห้องสมุด'],
                ['key' => 'admin.library.books', 'label' => 'จัดการห้องสมุด'],
                ['key' => 'admin.library.categories', 'label' => 'ตั้งค่าหมวดหมู่หนังสือ'],
                ['key' => 'admin.library.reports', 'label' => 'รายงานห้องสมุด (หนังสือ/ค้างส่ง/ยืม-คืน/ชำรุด/ผู้ยืมสูงสุด/สถิติเข้าใช้)'],
            ],
            'ตั้งค่า/อื่นๆ' => [
                ['key' => 'settings.school.index', 'label' => 'ตั้งค่าเริ่มต้น'],
                ['key' => 'settings.activity-timestamps', 'label' => 'ไทม์สแตมป์'],
                ['key' => 'settings.contact', 'label' => 'ติดต่อ-แจ้งปัญหา'],
            ],
        ];
    }
}
