document.addEventListener('DOMContentLoaded', function () {
  const inventorySubmenu = document.getElementById('inventorySubmenu');
  if (inventorySubmenu) {
    inventorySubmenu.addEventListener('click', function (e) {
      e.stopPropagation(); // Prevent the dropdown from closing
    });
  }
});