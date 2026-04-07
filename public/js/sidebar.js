document.querySelectorAll('.dropdown-toggle').forEach(button => {
    button.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation(); // สำคัญมาก: ป้องกันไม่ให้คลิกชั้นในแล้วไปปิดชั้นนอก
        
        const parent = button.parentElement;
        parent.classList.toggle('active');
    });
});