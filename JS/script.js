function homePage(){
        window.location.href = "home.html";
    };

function community(){
    window.location.href = "Community.html";
};

function profile() {
    window.location.href = "ProfileManagement/profile.php";
}


function openTerms(){
    window.open('https://policies.google.com/terms')
}

function openPolicy(){
    window.open('https://policies.google.com/terms')
}



function changeColor1() {
    const buttons = document.querySelectorAll(".btn-1");
  
    for (let i = 0; i < buttons.length; i++) {
      buttons[i].addEventListener("click", function () {
        buttons[i].style.backgroundColor = "green";
        buttons[i].innerText = 'Added'
      });
    }
}

function changeColor2() {
    const buttons = document.querySelectorAll(".btn-2");
  
    for (let i = 0; i < buttons.length; i++) {
      buttons[i].addEventListener("click", function () {
        buttons[i].style.backgroundColor = "gray";
        buttons[i].innerText = 'Joined'
      });
    }
}
function changeColor3() {
    const buttons = document.querySelectorAll(".btn-3");
  
    for (let i = 0; i < buttons.length; i++) {
      buttons[i].addEventListener("click", function () {
        buttons[i].style.backgroundColor = "gray";
        buttons[i].innerText = 'Following'
      });
    }
}








