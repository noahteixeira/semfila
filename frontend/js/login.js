var params = new URLSearchParams(window.location.search);

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").style.display = "block";
}
