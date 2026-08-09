{{--
    ช่องเลือกครูผู้สอน 1 คน (searchable combo) — ใช้ซ้ำได้หลายช่องเพื่อเลือกครูผู้สอนได้มากกว่า 1 คน
    ต้องส่ง $prefix มา (เช่น "add_0_", "edit_1_") ให้ id ไม่ชนกันเมื่อมีหลายช่องในหน้าเดียว
    ใช้ฟังก์ชัน JS ชุดเดิม (openPersonnelCombo/filterPersonnelCombo/selectPersonnelCombo) ที่รับ prefix อยู่แล้ว
--}}
<div class="cf-combo" id="{{ $prefix }}personnel_combo">
    <input type="text" id="{{ $prefix }}personnel_search" class="cf-combo-input" autocomplete="off"
        placeholder="พิมพ์ชื่อครูเพื่อค้นหา..."
        onfocus="openPersonnelCombo('{{ $prefix }}')" oninput="filterPersonnelCombo('{{ $prefix }}')">
    <input type="hidden" name="personnel_ids[]" id="{{ $prefix }}personnel_id">
    <div class="cf-combo-list" id="{{ $prefix }}personnel_list">
        <div class="cf-combo-item" data-id="" data-search="" data-label="-- ยังไม่กำหนด --" onclick="selectPersonnelCombo('{{ $prefix }}', this)">-- ยังไม่กำหนด --</div>
        @foreach($personnels ?? [] as $p)
        <div class="cf-combo-item"
            data-id="{{ $p->personnel_id }}"
            data-search="{{ \Illuminate\Support\Str::lower(($p->thai_prefix ?? '') . $p->thai_firstname . ' ' . $p->thai_lastname) }}"
            data-label="{{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}"
            onclick="selectPersonnelCombo('{{ $prefix }}', this)">
            {{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
        </div>
        @endforeach
        <div class="cf-combo-empty" id="{{ $prefix }}personnel_empty" style="display:none">ไม่พบครูที่ค้นหา</div>
    </div>
</div>
