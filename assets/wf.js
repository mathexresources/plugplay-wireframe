// Shared chrome for every wireframe page: header, journey stepper, footer, wireframe nav.
// Each page sets <body data-step="N"> (0 = classic catalogue, 1–5 = journey steps, 6 = order done).
// ?z=katalog on a journey page (product detail) switches it to the classic, no-tour variant.

const STEPS = [
  { href: 'index.html',      label: 'Tvoje auto' },
  { href: 'garaz.html',      label: 'Co vylepšit' },
  { href: 'produkt.html',    label: 'Výběr produktu' },
  { href: 'plan.html',       label: 'Tvůj plán' },
  { href: 'objednavka.html', label: 'Objednávka' },
];

const classic = new URLSearchParams(location.search).get('z') === 'katalog';
const step = classic ? 0 : Number(document.body.dataset.step || 0);

// Pages mark tour-only / classic-only blocks with data-mode="tour" | "katalog".
document.querySelectorAll('[data-mode]').forEach(el =>
  el.classList.toggle('d-none', el.dataset.mode !== (classic ? 'katalog' : 'tour')));

// Fake login state: visiting ucet.html logs you in for the rest of the click-through, "Odhlásit se" logs you out.
const store = (key, val) => {
  try { return val === undefined ? sessionStorage.getItem(key) : (val === null ? sessionStorage.removeItem(key) : sessionStorage.setItem(key, val)); }
  catch { return null; }
};
if (document.body.dataset.auth === '1') store('wf-auth', '1');
const loggedIn = store('wf-auth') === '1';

// Blocks shown only to logged-in users (data-auth="in") or only to guests (data-auth="out").
document.querySelectorAll('[data-auth="in"], [data-auth="out"]').forEach(el =>
  el.classList.toggle('d-none', (el.dataset.auth === 'in') !== loggedIn));

const CARS = [
  { nick: 'Moje E46', name: 'BMW 320d Touring (E46)', years: '2003', active: true },
  { nick: 'Rodinná Fabia', name: 'Škoda Fabia I', years: '2005' },
];

const CATEGORIES = ['Nabíjení a USB', 'Rádio, Bluetooth a zvuk', 'Osvětlení', 'Zamykání a zabezpečení',
  'Parkovací senzory a kamery', 'Baterie a monitoring', 'Diagnostika a kódování', 'Kabeláže a konektory', 'Nářadí'];

const carPill = () => `
  <div class="dropdown">
    <button class="wf-car-pill dropdown-toggle" data-bs-toggle="dropdown">
      <i class="bi bi-car-front-fill"></i> BMW řady 3 (E46) <span class="text-secondary">1998–2006</span>
    </button>
    <div class="dropdown-menu p-2" style="min-width:280px">
      ${loggedIn ? `
        <div class="small fw-bold text-secondary px-2 mb-1">Moje garáž</div>
        ${CARS.map(c => `
          <a class="dropdown-item rounded d-flex align-items-center gap-2 ${c.active ? 'active' : ''}" href="garaz.html">
            <i class="bi ${c.active ? 'bi-check-circle-fill' : 'bi-circle'}"></i>
            <span><b>${c.nick}</b><br><span class="small ${c.active ? '' : 'text-secondary'}">${c.name} · ${c.years}</span></span>
          </a>`).join('')}
        <hr class="dropdown-divider">
        <a class="dropdown-item rounded" href="ucet.html#garaz"><i class="bi bi-plus-lg"></i> Přidat nebo upravit auta</a>
      ` : `
        <a class="dropdown-item rounded" href="index.html"><i class="bi bi-arrow-repeat"></i> Změnit auto</a>
        <hr class="dropdown-divider">
        <div class="small px-2 text-secondary">Máš víc aut? <a href="prihlaseni.html">Přihlas se</a> a ulož si je do garáže.</div>
      `}
    </div>
  </div>`;

const accountMenu = () => `
  <div class="dropdown">
    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="dropdown" aria-label="Můj účet">
      <i class="bi ${loggedIn ? 'bi-person-check-fill' : 'bi-person'}"></i><span class="d-none d-lg-inline"> ${loggedIn ? 'Petr' : 'Přihlásit se'}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      ${loggedIn ? `
        <li><a class="dropdown-item" href="ucet.html#garaz"><i class="bi bi-car-front"></i> Moje garáž</a></li>
        <li><a class="dropdown-item" href="ucet.html#objednavky"><i class="bi bi-box-seam"></i> Objednávky</a></li>
        <li><a class="dropdown-item" href="ucet.html#navody"><i class="bi bi-book"></i> Moje návody</a></li>
        <li><a class="dropdown-item" href="ucet.html#plany"><i class="bi bi-bookmark"></i> Uložené plány</a></li>
        <li><a class="dropdown-item" href="ucet.html#udaje"><i class="bi bi-person"></i> Osobní údaje</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="index.html" data-logout><i class="bi bi-box-arrow-right"></i> Odhlásit se</a></li>
      ` : `
        <li><a class="dropdown-item" href="prihlaseni.html">Přihlásit se</a></li>
        <li><a class="dropdown-item" href="prihlaseni.html">Založit účet</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><span class="dropdown-item-text small text-secondary">Ulož si auta do garáže<br>a měj návody vždy po ruce.</span></li>
      `}
    </ul>
  </div>`;

document.getElementById('wf-header').innerHTML = `
  <header class="bg-white border-bottom">
    <div class="container py-2 d-flex flex-wrap align-items-center gap-3">
      <a href="index.html" class="wf-logo" aria-label="PlugPlay.cz – úvodní stránka">Plug<b>Play</b>.cz</a>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-grid"></i> Katalog</button>
        <ul class="dropdown-menu">
          ${CATEGORIES.map(c => `<li><a class="dropdown-item" href="katalog.html">${c}</a></li>`).join('')}
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item fw-bold" href="katalog.html">Všechny produkty →</a></li>
        </ul>
      </div>
      ${step >= 2 || loggedIn ? carPill() : ''}
      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="input-group input-group-sm d-none d-md-flex" style="width:240px">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input class="form-control" placeholder="Hledat produkt nebo návod…" aria-label="Hledat">
        </div>
        <a href="#" class="btn btn-sm btn-outline-dark d-none d-sm-inline-block"><i class="bi bi-book"></i> Návody</a>
        ${accountMenu()}
        <a href="plan.html" class="btn btn-sm btn-dark position-relative">
          <i class="bi ${step === 0 ? 'bi-cart3' : 'bi-list-check'}"></i> ${step === 0 ? 'Košík' : 'Plán'}
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-primary">${step >= 3 ? 2 : 0}</span>
        </a>
      </div>
    </div>
  </header>
  ${step >= 1 && step <= 5 ? `
  <nav class="wf-journey" aria-label="Postup výběru a objednávky">
    <div class="container"><ol>
      ${STEPS.map((s, i) => {
        const n = i + 1;
        const cls = n < step ? 'done' : n === step ? 'active' : '';
        return `<li class="${cls}"><a href="${s.href}"><span class="num">${n < step ? '<i class="bi bi-check-lg"></i>' : n}</span>${s.label}</a></li>`;
      }).join('')}
    </ol></div>
  </nav>` : ''}
`;

document.getElementById('wf-footer').innerHTML = `
  <footer class="border-top bg-white mt-5 py-4">
    <div class="container">
      <div class="row g-4 small">
        <div class="col-12 col-md-3">
          <div class="wf-logo fs-5">Plug<b>Play</b>.cz</div>
          <p class="text-secondary mt-2 mb-0">Elektronika do starších aut, kterou zapojíš sám. Testujeme, píšeme návody a radíme s výběrem.</p>
        </div>
        <div class="col-6 col-md-2"><b>Nákup</b><div>Doprava a platba</div><div>Vrácení a reklamace</div><div>Obchodní podmínky</div><div>Ochrana osobních údajů</div></div>
        <div class="col-6 col-md-3"><b>Poradna</b><div>Návody podle auta</div><div>Video návody</div><div>Jak ověřujeme kompatibilitu</div><div>Potřebné nářadí</div></div>
        <div class="col-6 col-md-2"><b>Oblíbené modely</b><div>BMW řady 3 (E46)</div><div>Škoda Octavia I</div><div>VW Golf IV</div><div>Škoda Fabia I</div></div>
        <div class="col-6 col-md-2"><b>Kontakt</b><div>info@plugplay.cz</div><div>Po–Pá 9:00–17:00</div><div>Poradíme s výběrem</div></div>
      </div>
      <div class="small text-secondary border-top mt-4 pt-3">© 2026 PlugPlay.cz</div>
    </div>
  </footer>
`;

// Floating wireframe navigator – not part of the design, just for clicking through the flow.
const here = page => location.pathname.endsWith(page) ? 'fw-bold' : 'opacity-75';
const dev = document.createElement('div');
dev.className = 'wf-dev d-flex flex-wrap align-items-center gap-2';
dev.innerHTML = `
  <b>WIREFRAME</b>
  ${['index', 'garaz', 'produkt', 'plan', 'objednavka', 'hotovo'].map((p, i) =>
    `<a href="${p}.html" class="${i + 1 === step ? 'fw-bold' : 'opacity-75'}">${i + 1}</a>`).join(' ')}
  <span class="opacity-50">|</span>
  <a href="katalog.html" class="${here('katalog.html')}">katalog</a>
  <a href="produkt.html?z=katalog" class="${classic ? 'fw-bold' : 'opacity-75'}">detail</a>
  <span class="opacity-50">|</span>
  <a href="prihlaseni.html" class="${here('prihlaseni.html')}">login</a>
  <a href="ucet.html" class="${here('ucet.html')}">účet</a>
  <span class="form-check form-switch m-0 ms-1">
    <input class="form-check-input" type="checkbox" id="wf-notes" checked>
    <label class="form-check-label" for="wf-notes">poznámky</label>
  </span>`;
document.body.appendChild(dev);
document.getElementById('wf-notes').addEventListener('change', e =>
  document.body.classList.toggle('wf-hide-notes', !e.target.checked));

document.querySelectorAll('[data-logout]').forEach(a => a.addEventListener('click', () => store('wf-auth', null)));

// Fake interactivity for chips / hotspots so the flow feels clickable.
document.querySelectorAll('[data-toggle-group]').forEach(group => {
  group.addEventListener('click', e => {
    const item = e.target.closest(group.dataset.toggleGroup);
    if (!item) return;
    group.querySelectorAll(group.dataset.toggleGroup).forEach(el => el.classList.remove('on', 'active'));
    item.classList.add(item.classList.contains('wf-hot') ? 'active' : 'on');
  });
});
