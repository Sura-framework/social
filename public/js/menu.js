const menuBtn = document.querySelector(".m-menu");
const menu=document.querySelector(".menu-sidebar");

menuBtn.addEventListener("click", function(){
    menu.classList.toggle("active");
// console.log("нажатие на кнопку");
})