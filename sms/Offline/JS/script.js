(function () {
  const root = document.documentElement;
  const LS_KEY = "bit_theme_v1";
  const loader = document.getElementById("site-loader");
  const floatBtn = document.getElementById("theme-toggle-floating");
  const headerCheckbox = document.getElementById("theme-switch-checkbox");

  // Apply saved theme or system preference
  function applyTheme(theme) {
    if (theme === "dark") {
      root.setAttribute("data-theme", "dark");
      if (headerCheckbox) headerCheckbox.checked = true;
    } else {
      root.setAttribute("data-theme", "light");
      if (headerCheckbox) headerCheckbox.checked = false;
    }
    try {
      localStorage.setItem(LS_KEY, theme);
    } catch (e) {}
  }

  // Determine initial theme
  try {
    const saved = localStorage.getItem(LS_KEY);
    if (saved) applyTheme(saved);
    else {
      // use prefers-color-scheme
      const prefersDark =
        window.matchMedia &&
        window.matchMedia("(prefers-color-scheme: dark)").matches;
      applyTheme(prefersDark ? "dark" : "light");
    }
  } catch (e) {
    applyTheme("light");
  }

  // Toggle function
  function toggleTheme() {
    const current =
      root.getAttribute("data-theme") === "dark" ? "dark" : "light";
    applyTheme(current === "dark" ? "light" : "dark");
  }

  // Attach events
  if (floatBtn) floatBtn.addEventListener("click", toggleTheme);
  if (headerCheckbox) headerCheckbox.addEventListener("change", toggleTheme);

  // Keyboard shortcut: T toggles theme (accessible)
  document.addEventListener("keydown", (e) => {
    if (e.key === "t" || e.key === "T") {
      toggleTheme();
    }
  });

  // Loader: hide when fully loaded
  function hideLoader() {
    if (!loader) return;
    loader.classList.add("hidden");
    setTimeout(() => {
      try {
        loader.setAttribute("aria-hidden", "true");
      } catch (e) {}
    }, 400);
  }

  // Wait for full load (images/fonts)
  if (document.readyState === "complete") {
    hideLoader();
  } else {
    window.addEventListener("load", hideLoader);
    // fallback: ensure loader hidden after 6s max
    setTimeout(hideLoader, 6000);
  }
})();

console.log("hello world");
