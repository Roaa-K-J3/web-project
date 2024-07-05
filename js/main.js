(function ($) {

    "use strict";

    var fullHeight = function () {

        $('.js-fullheight').css('height', $(window).height());
        $(window).resize(function () {
            $('.js-fullheight').css('height', $(window).height());
        });

    };
    fullHeight();

    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });

})(jQuery);

(() => {
    document.querySelectorAll('.logout').forEach((item) => {
        item.addEventListener('click', () => {
            logout();
        })
    });

    checkIfUserIsLoggedIn();
    const user = JSON.parse(localStorage.getItem("user")) || null;
    if (user) {
        document.querySelector('nav > div > a.img').style.backgroundImage = `url(./php/${user.avatar})`;
        console.log(user.avatar)
        document.querySelectorAll('.profile').forEach((item) => {
            item.href = `./edit-user.html?id=${user.id}`;
        });
        if(user.is_admin == 0){
            document.querySelectorAll('.add-user').forEach((item) => {
                item.style.display = "none";
            });
        }
    }
})();


async function logout() {
    // fetch
    const res = await fetch('./php/logout.php');
    if (res.ok) {
        localStorage.removeItem("user");
        window.location.href = "./login.html";
    }
}

async function checkIfUserIsLoggedIn() {
    const res = await fetch('./php/is_user_logged_in.php');
    if (res.ok) {
        if (window.location.href.indexOf("login.html") > -1) {
            window.location.href = "./index.html";
        }
    } else if (window.location.href.indexOf("login.html") === -1){
        window.location.href = "./login.html";
    }
}

