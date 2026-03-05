import Alpine from 'alpinejs';

window.createUnifiedUploadState = function createUnifiedUploadState() {
  return {
    fileQueue: [],
    uploading: false,
    uploadProgress: 0,

    openPicker() {
      if (!this.$refs.picker) return;
      this.$refs.picker.value = '';
      this.$refs.picker.click();
    },

    onPick(event) {
      this.addFiles(event.target.files || []);
      event.target.value = '';
    },

    onDrop(event) {
      this.addFiles((event.dataTransfer && event.dataTransfer.files) || []);
    },

    addFiles(fileList) {
      Array.from(fileList).forEach((file) => {
        const kind = this.kindFor(file);
        const preview = kind === 'documents' ? '' : URL.createObjectURL(file);

        this.fileQueue.push({
          id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
          kind,
          file,
          preview,
          name: file.name,
        });
      });

      this.syncInputs();
    },

    kindFor(file) {
      const mime = (file && file.type) || '';
      if (mime.startsWith('image/')) return 'photos';
      if (mime.startsWith('video/')) return 'videos';
      return 'documents';
    },

    removeFile(id) {
      const index = this.fileQueue.findIndex((item) => item.id === id);
      if (index < 0) return;

      const [removed] = this.fileQueue.splice(index, 1);
      if (removed && removed.preview) {
        URL.revokeObjectURL(removed.preview);
      }

      this.syncInputs();
    },

    countByKind(kind) {
      return this.fileQueue.filter((item) => item.kind === kind).length;
    },

    clearUploadState() {
      this.fileQueue.forEach((item) => {
        if (item.preview) {
          URL.revokeObjectURL(item.preview);
        }
      });

      this.fileQueue = [];
      this.uploading = false;
      this.uploadProgress = 0;
      this.syncInputs();
    },

    syncInputs() {
      const grouped = {
        photos: new DataTransfer(),
        videos: new DataTransfer(),
        documents: new DataTransfer(),
      };

      this.fileQueue.forEach((item) => {
        grouped[item.kind].items.add(item.file);
      });

      if (this.$refs.photosInput) this.$refs.photosInput.files = grouped.photos.files;
      if (this.$refs.videosInput) this.$refs.videosInput.files = grouped.videos.files;
      if (this.$refs.documentsInput) this.$refs.documentsInput.files = grouped.documents.files;
    },

    submitWithProgress(event) {
      event.preventDefault();
      if (this.uploading) return;

      this.syncInputs();

      const form = event.currentTarget;
      if (!(form instanceof HTMLFormElement)) return;

      const method = (form.getAttribute('method') || 'POST').toUpperCase();
      const action = form.getAttribute('action') || window.location.href;
      const payload = new FormData(form);

      this.uploading = true;
      this.uploadProgress = 0;

      const request = new XMLHttpRequest();
      request.open(method, action, true);
      request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      request.setRequestHeader('Accept', 'text/html,application/xhtml+xml');

      request.upload.onprogress = (e) => {
        if (!e.lengthComputable) return;
        this.uploadProgress = Math.max(0, Math.min(100, Math.round((e.loaded / e.total) * 100)));
      };

      request.onload = () => {
        this.uploading = false;
        this.uploadProgress = 100;

        if (request.status >= 200 && request.status < 400) {
          window.location.href = request.responseURL || action;
          return;
        }

        // For validation or unexpected errors, fallback to normal submit to preserve Laravel behavior.
        form.submit();
      };

      request.onerror = () => {
        this.uploading = false;
        this.uploadProgress = 0;
        form.submit();
      };

      request.send(payload);
    },
  };
};

window.Alpine = Alpine;
Alpine.start();
