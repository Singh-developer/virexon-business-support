
<div id="filePreviewModal" style="display:none; position:fixed; inset:0; z-index:300; background:rgba(15,23,42,.78); padding:12px; box-sizing:border-box;" role="dialog" aria-modal="true" aria-label="File preview">
    <div style="max-width:920px; height:100%; max-height:92vh; margin:4vh auto; background:#fff; border-radius:14px; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,.35);">
        <div style="display:flex; align-items:center; gap:10px; padding:12px 16px; border-bottom:1px solid #e2e8f0; flex-shrink:0;">
            <div id="filePreviewModalTitle" style="font-weight:700; font-size:13px; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1;">File preview</div>
            <a id="filePreviewModalDownload" href="#" class="btn secondary tiny" style="text-decoration:none; flex-shrink:0;">Download</a>
            <button type="button" id="filePreviewModalClose" class="btn secondary tiny" style="flex-shrink:0;">✕ Close</button>
        </div>
        <div id="filePreviewModalBody" style="flex:1; overflow:auto; background:#f8fafc; -webkit-overflow-scrolling:touch;">
            <img id="filePreviewModalImg" src="" alt="File preview" style="display:none; width:100%; object-fit:contain; background:#fff;">
            <iframe id="filePreviewModalFrame" src="" style="display:none; width:100%; height:100%; min-height:70vh; border:0; background:#fff;" title="File preview"></iframe>
        </div>
    </div>
</div>

<script>
window.FilePreviewModal = (function () {
    var modal = document.getElementById('filePreviewModal');
    var img = document.getElementById('filePreviewModalImg');
    var frame = document.getElementById('filePreviewModalFrame');
    var title = document.getElementById('filePreviewModalTitle');
    var download = document.getElementById('filePreviewModalDownload');
    var closeBtn = document.getElementById('filePreviewModalClose');

    function isImageExt(ext) {
        return ['jpg', 'jpeg', 'png', 'webp'].indexOf(String(ext || '').toLowerCase()) !== -1;
    }

    function open(opts) {
        if (!modal) return;
        opts = opts || {};
        if (title) title.textContent = opts.name || 'File preview';
        if (download) {
            if (opts.downloadUrl) { download.href = opts.downloadUrl; download.style.display = ''; }
            else { download.style.display = 'none'; }
        }
        if (isImageExt(opts.ext)) {
            frame.style.display = 'none';
            frame.src = 'about:blank';
            img.src = opts.url;
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
            img.removeAttribute('src');
            frame.src = opts.url;
            frame.style.display = 'block';
        }
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        var body = document.getElementById('filePreviewModalBody');
        if (body) body.scrollTop = 0;
    }

    function close() {
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
        try { frame.src = 'about:blank'; } catch (e) {}
        try { img.removeAttribute('src'); } catch (e) {}
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) close();
        });
    }
    if (closeBtn) closeBtn.addEventListener('click', close);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'block') close();
    });

    return {
        open: open,
        close: close,
        usePopup: function () {
            return window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
        }
    };
})();
</script>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/partials/file-preview-modal.blade.php ENDPATH**/ ?>