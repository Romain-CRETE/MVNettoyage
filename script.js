//menu burger

const burger = document.querySelector('.burger');
const nav = document.querySelector('.menu ul');

if (burger && nav) {
    burger.addEventListener('click', () => {
        nav.classList.toggle('active');
    });
}

// -----------------------------
// CARROUSEL AUTOMATIQUE GALERIE
// -----------------------------

const galleryImages = document.querySelectorAll('.gallery img');
let currentIndex = 0;           // index pour le carrousel
const interval = 3000;          // défilement toutes les 3 secondes

// Fonction pour afficher l'image du carrousel
function showImage(index) {
    galleryImages.forEach((img, i) => {
        img.classList.remove('active');
    });
    galleryImages[index].classList.add('active');
}

// Afficher la première image
showImage(currentIndex);

// Fonction pour passer à l'image suivante
function nextImage() {
    currentIndex = (currentIndex + 1) % galleryImages.length;
    showImage(currentIndex);
}

// Lancer le carrousel automatique
setInterval(nextImage, interval);

// -----------------------------
// LIGHTBOX AU CLIC
// -----------------------------

const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');
const closeBtn = document.querySelector('.lightbox .close');
const prevBtn = document.getElementById('prev');
const nextBtn = document.getElementById('next');

let currentLightboxIndex = 0;

// Ouvrir lightbox au clic sur une image
galleryImages.forEach((img, index) => {
    img.addEventListener('click', () => {
        lightbox.style.display = 'flex';
        lightboxImg.src = img.src;
        currentLightboxIndex = index;
    });
});

// Fermer lightbox
closeBtn.addEventListener('click', () => {
    lightbox.style.display = 'none';
});

// Navigation lightbox avec flèches
function showLightboxImage(index) {
    if (index < 0) index = galleryImages.length - 1;
    if (index >= galleryImages.length) index = 0;
    lightboxImg.src = galleryImages[index].src;
    currentLightboxIndex = index;
}

prevBtn.addEventListener('click', () => showLightboxImage(currentLightboxIndex - 1));
nextBtn.addEventListener('click', () => showLightboxImage(currentLightboxIndex + 1));

// Navigation clavier
document.addEventListener('keydown', (e) => {
    if (lightbox.style.display === 'flex') {
        if (e.key === 'ArrowLeft') showLightboxImage(currentLightboxIndex - 1);
        if (e.key === 'ArrowRight') showLightboxImage(currentLightboxIndex + 1);
        if (e.key === 'Escape') lightbox.style.display = 'none';
    }
});


