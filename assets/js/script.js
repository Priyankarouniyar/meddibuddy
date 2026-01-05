function openModal(id) {
  document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}
// Close modal on outside click
window.onclick = function(event) {
  const modal = document.querySelector('.modal');
  if (event.target == modal) {
      modal.style.display = 'none';
  }
}
