(function () {
    function getSelectedLang() {
        var body = document.body;

        if (body && body.getAttribute('data-lang')) {
            return body.getAttribute('data-lang');
        }

        try {
            return localStorage.getItem('revamppage_lang') || 'zh';
        } catch (e) {
            return 'zh';
        }
    }

    function updateOtherInformationTitle() {
        var lang = getSelectedLang();
        var titleCn = document.querySelector('.revamppage-other-information .title-cn');
        var titleEn = document.querySelector('.revamppage-other-information .title-eng');

        if (!titleCn && !titleEn) {
            return;
        }

        if (lang === 'en') {
            if (titleCn) {
                titleCn.style.display = 'none';
            }
            if (titleEn) {
                titleEn.style.display = 'inline-block';
            }
        } else {
            if (titleCn) {
                titleCn.style.display = 'inline-block';
            }
            if (titleEn) {
                titleEn.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', updateOtherInformationTitle);
    document.addEventListener('revamppage:languageChanged', updateOtherInformationTitle);
    updateOtherInformationTitle();
})();