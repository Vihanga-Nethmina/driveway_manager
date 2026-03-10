document.addEventListener("DOMContentLoaded", () => {
  const els = document.querySelectorAll(".animate-on-scroll");
  const obs = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) e.target.classList.add("show");
    });
  }, { threshold: 0.15 });

  els.forEach((el) => obs.observe(el));
});
