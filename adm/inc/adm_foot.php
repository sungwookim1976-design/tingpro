        </div>
    </div>
</div>
<div class="adm-toast" id="admToast"></div>
<script>
    function admToast(msg) {
        var el = document.getElementById('admToast');
        el.textContent = msg;
        el.classList.add('show');
        clearTimeout(window._admToastTimer);
        window._admToastTimer = setTimeout(function () { el.classList.remove('show'); }, 2200);
    }
</script>
</body>
</html>
