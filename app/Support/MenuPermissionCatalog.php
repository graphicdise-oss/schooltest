<?php

namespace App\Support;

/**
 * รายการเมนูทั้งหมดที่กำหนดสิทธิ์การเข้าถึงได้ — ใช้ร่วมกันระหว่างหน้า "กำหนดสิทธิ์" ของตำแหน่ง
 * (PositionController) และของประเภทบุคลากร (PersonnelTypeController — เดิมใช้งานจริง ตอนนี้ย้ายมาที่
 * ตำแหน่งแล้ว โค้ดยังเก็บไว้เผื่ออ้างอิง ไม่ได้ผูกกับการเช็คสิทธิ์จริงอีกต่อไป) เก็บไว้ที่เดียวกันกัน
 * เมนูใหม่ที่เพิ่มทีหลังตกหล่นไม่ตรงกันระหว่าง 2 จุด
 */
class MenuPermissionCatalog
{
    public static function list(): array
    {
        return [
            'ข้อมูลบุคคล' => [
                ['key' => 'students.index', 'label' => 'ข้อมูลนักเรียน'],
                ['key' => 'students.create', 'label' => 'เพิ่มนักเรียน'],
                ['key' => 'personnels.index', 'label' => 'ข้อมูลบุคลากร'],
                ['key' => 'personnels.create', 'label' => 'เพิ่มบุคลากร'],
            ],
            'วิชาการ' => [
                ['key' => 'academic.curriculum', 'label' => 'จัดการหลักสูตร'],
                ['key' => 'academic.timetable', 'label' => 'ตารางสอน'],
                ['key' => 'academic.scores', 'label' => 'บันทึกคะแนน'],
                ['key' => 'academic.reports', 'label' => 'รายงานวิชาการ'],
            ],
            'กิจการนักเรียน' => [
                ['key' => 'affairs.attendance', 'label' => 'เช็คชื่อ/ลา'],
                ['key' => 'affairs.behavior', 'label' => 'ความประพฤติ'],
                ['key' => 'affairs.homevisit', 'label' => 'เยี่ยมบ้าน'],
            ],
            'บริหารทั่วไป' => [
                ['key' => 'admin.news', 'label' => 'ประชาสัมพันธ์'],
                ['key' => 'admin.library', 'label' => 'ห้องสมุด'],
                ['key' => 'admin.bus', 'label' => 'School Bus'],
            ],
            'บัญชี/การเงิน' => [
                ['key' => 'finance.income', 'label' => 'ระบบบัญชีรายรับ'],
                ['key' => 'finance.expense', 'label' => 'ระบบบัญชีรายจ่าย'],
                ['key' => 'finance.salary', 'label' => 'ระบบเงินเดือน'],
                ['key' => 'finance.reports', 'label' => 'รายงานบัญชี'],
            ],
            'ตั้งค่า' => [
                ['key' => 'settings.prefix', 'label' => 'ตั้งค่าคำนำหน้า'],
                ['key' => 'settings.personnel_type', 'label' => 'ตั้งค่าประเภทบุคลากร'],
                ['key' => 'settings.general', 'label' => 'ตั้งค่าทั่วไป'],
            ],
        ];
    }
}
