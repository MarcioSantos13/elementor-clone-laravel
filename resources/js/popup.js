class PopupManager {
    constructor() {
        this.openPopups = [];
        this.init();
    }

    init() {
        document.querySelectorAll('.pb-popup-overlay').forEach(popup => {
            this.setupPopup(popup);
        });
    }

    setupPopup(popup) {
        const triggers = JSON.parse(popup.dataset.triggers || '[]');
        const closeBtn = popup.querySelector('.pb-popup-close');
        const content = popup.querySelector('.pb-popup-content');

        if (closeBtn) {
            closeBtn.onclick = () => this.close(popup);
        }

        popup.onclick = (e) => {
            if (e.target === popup) this.close(popup);
        };

        triggers.forEach(trigger => {
            switch (trigger.type) {
                case 'on_load':
                    document.addEventListener('DOMContentLoaded', () => this.open(popup));
                    break;
                case 'on_timer':
                    setTimeout(() => this.open(popup), (trigger.value || 3) * 1000);
                    break;
                case 'on_scroll':
                    window.addEventListener('scroll', () => {
                        if (!popup._triggered) {
                            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                            if (scrollPercent >= (parseFloat(trigger.value) || 50)) {
                                popup._triggered = true;
                                this.open(popup);
                            }
                        }
                    }, { once: false });
                    break;
                case 'on_exit':
                    document.addEventListener('mouseleave', (e) => {
                        if (e.clientY <= 0 && !popup._triggered) {
                            popup._triggered = true;
                            this.open(popup);
                        }
                    });
                    break;
                case 'on_click':
                    if (trigger.value) {
                        document.querySelectorAll(trigger.value).forEach(el => {
                            el.addEventListener('click', () => this.open(popup));
                        });
                    }
                    break;
            }
        });
    }

    open(popup) {
        if (this.openPopups.length > 0) return;
        popup.style.display = 'flex';
        this.openPopups.push(popup);
        document.body.style.overflow = 'hidden';
    }

    close(popup) {
        popup.style.display = 'none';
        this.openPopups = this.openPopups.filter(p => p !== popup);
        if (this.openPopups.length === 0) {
            document.body.style.overflow = '';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window._popupManager = new PopupManager();
});
