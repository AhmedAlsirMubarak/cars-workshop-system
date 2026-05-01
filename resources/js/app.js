import Alpine from 'alpinejs'
import focus  from '@alpinejs/focus'

Alpine.plugin(focus)

// ── Global Alpine components ──────────────────────────────────

// Flash message auto-dismiss
Alpine.data('flash', () => ({
    show: true,
    init() { setTimeout(() => this.show = false, 4000) },
}))

// Sidebar state (persisted in localStorage)
Alpine.data('sidebar', () => ({
    open:     localStorage.getItem('sidebar') !== 'closed',
    mobileOpen: false,
    toggle()       { this.open = !this.open; localStorage.setItem('sidebar', this.open ? 'open' : 'closed') },
    openMobile()   { this.mobileOpen = true },
    closeMobile()  { this.mobileOpen = false },
}))

// Generic dropdown
Alpine.data('dropdown', () => ({
    open: false,
    toggle() { this.open = !this.open },
    close()  { this.open = false },
}))

// Modal
Alpine.data('modal', (initialOpen = false) => ({
    open: initialOpen,
    show() { this.open = true },
    hide() { this.open = false },
}))

// Confirm-delete dialog
Alpine.data('confirmDelete', (url) => ({
    open: false,
    url,
    init() {
        window.addEventListener('open-confirm-delete', (e) => {
            this.url  = e.detail
            this.open = true
        })
    },
    show() { this.open = true },
    hide() { this.open = false },
    confirm() {
        const form = document.createElement('form')
        form.method = 'POST'
        form.action = this.url
        form.innerHTML = `
            <input type="hidden" name="_token"  value="${document.querySelector('meta[name=csrf-token]').content}">
            <input type="hidden" name="_method" value="DELETE">
        `
        document.body.appendChild(form)
        form.submit()
    },
}))

// Search with debounce (submits the closest form)
Alpine.data('searchBox', () => ({
    query: new URLSearchParams(window.location.search).get('search') ?? '',
    timer: null,
    debounce() {
        clearTimeout(this.timer)
        this.timer = setTimeout(() => this.$el.closest('form').submit(), 400)
    },
}))

// Number formatter (OMR)
window.fmtOMR = (val) =>
    new Intl.NumberFormat('en-OM', { style: 'currency', currency: 'OMR', minimumFractionDigits: 3 })
        .format(val ?? 0)

window.Alpine = Alpine
Alpine.start()
