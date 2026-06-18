import './bootstrap';

import Alpine from 'alpinejs';
import { registerSW } from 'virtual:pwa-register';
import { Html5Qrcode } from 'html5-qrcode';

window.Alpine = Alpine;
window.Html5Qrcode = Html5Qrcode;

Alpine.start();

registerSW({
	immediate: true,
});
