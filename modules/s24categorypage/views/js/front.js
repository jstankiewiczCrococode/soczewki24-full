document.addEventListener("DOMContentLoaded", () => {
  console.log("S24 Category Page JS działa");

  const checkboxes = document.querySelectorAll(
    '.checbox-list input[type="checkbox"]',
  );

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      console.log(checkbox.value, checkbox.checked);
    });
  });
});
