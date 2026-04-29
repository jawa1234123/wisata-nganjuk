<?php
include 'config.php';

$wisata = mysqli_query($conn,"SELECT * FROM wisata ORDER BY id DESC LIMIT 8");
$kuliner = mysqli_query($conn,"SELECT * FROM kuliner ORDER BY id DESC LIMIT 8");
$event   = mysqli_query($conn,"SELECT * FROM event ORDER BY id DESC LIMIT 8");
?>

<!DOCTYPE html>
<html>
<head>
<title>Nganjuk Explore</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#020617;
    color:white;
}

/* HERO */
.hero{
    height:100vh;
    position:relative;
}
.hero video{
    width:100%;
    height:100%;
    object-fit:cover;
}
.hero-overlay{
    position:absolute;
    width:100%;
    height:100%;
    background:linear-gradient(to right, rgba(0,0,0,0.8), transparent);
}
.hero-content{
    position:absolute;
    top:50%;
    left:8%;
    transform:translateY(-50%);
}
.hero-content h1{
    font-size:64px;
    font-weight:bold;
}

/* SECTION */
.section{
    padding:80px 5%;
    position:relative;
    overflow:hidden;
}

/* ORNAMEN BACKGROUND */
.section::before{
    content:'';
    position:absolute;
    width:400px;
    height:400px;
    background:radial-gradient(circle, rgba(255,255,255,0.08), transparent);
    top:-100px;
    right:-100px;
    filter:blur(80px);
}

/* WARNA */
.section-wisata{
    background:linear-gradient(135deg,#0f172a,#1e3a8a);
}
.section-kuliner{
    background:linear-gradient(135deg,#7c2d12,#ea580c);
}
.section-event{
    background:linear-gradient(135deg,#4c1d95,#7c3aed);
}

/* HEADER */
.section-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    position:relative;
}

.section-header h2{
    position:relative;
    padding-left:15px;
}

.section-header h2::before{
    content:'';
    position:absolute;
    left:0;
    top:5px;
    width:4px;
    height:80%;
    background:linear-gradient(to bottom,#22c55e,#4ade80);
    border-radius:5px;
    box-shadow:0 0 10px #22c55e;
}

/* SLIDER */
.slider-wrapper{
    position:relative;
}

.slider{
    display:flex;
    gap:20px;
    overflow-x:auto;
    scroll-behavior:smooth;
    scroll-snap-type:x mandatory;
}

.slider::-webkit-scrollbar{
    display:none;
}

/* FADE EDGE */
.slider-wrapper::before,
.slider-wrapper::after{
    content:'';
    position:absolute;
    top:0;
    width:80px;
    height:100%;
    z-index:5;
    pointer-events:none;
}
.slider-wrapper::before{
    left:0;
    background:linear-gradient(to right, rgba(0,0,0,0.8), transparent);
}
.slider-wrapper::after{
    right:0;
    background:linear-gradient(to left, rgba(0,0,0,0.8), transparent);
}

/* CARD */
.card{
    min-width:300px;
    height:260px;
    border-radius:18px;
    overflow:hidden;
    position:relative;
    flex:0 0 auto;
    scroll-snap-align:start;
    transition:0.4s;
    box-shadow:0 10px 30px rgba(0,0,0,0.6);
    text-decoration:none;
    color:white;
}

.card:hover{
    transform:translateY(-8px) scale(1.03);
}

/* IMAGE */
.card img{
    position:absolute;
    width:100%;
    height:100%;
    object-fit:cover;
}

/* OVERLAY */
.card::after{
    content:'';
    position:absolute;
    width:100%;
    height:100%;
    background:linear-gradient(
        to top,
        rgba(0,0,0,0.95),
        rgba(0,0,0,0.3),
        transparent
    );
}

/* TEXT */
.card-content{
    position:relative;
    z-index:2;
    padding:15px;
}
.card-content h5{
    font-weight:600;
    text-shadow:0 2px 10px rgba(0,0,0,0.8);
}

/* BUTTON */
.nav-btn{
    position:absolute;
    right:0;
    top:-50px;
}
.nav-btn button{
    background:rgba(0,0,0,0.7);
    border:1px solid rgba(255,255,255,0.2);
    backdrop-filter:blur(8px);
    color:white;
    padding:10px 14px;
    margin-left:5px;
    border-radius:10px;
    transition:0.3s;
}
.nav-btn button:hover{
    background:#22c55e;
    transform:scale(1.1);
}
</style>
</head>

<body>

<!-- HERO -->
<section class="hero">
    <video autoplay muted loop>
        <source src="assets/video/bg.mp4" type="video/mp4">
    </video>

    <div class="hero-overlay"></div>

    <div class="hero-content">
        <h1>Explore Nganjuk</h1>
        <p>Wisata • Kuliner • Event terbaik</p>
    </div>
</section>

<?php function sliderSection($title,$data,$id,$type){ ?>
<section class="section section-<?= $type ?>">
    <div class="section-header">
        <h2><?= $title ?></h2>

        <div class="nav-btn">
            <button onclick="slideLeft('<?= $id ?>')">‹</button>
            <button onclick="slideRight('<?= $id ?>')">›</button>
        </div>
    </div>

    <div class="slider-wrapper">
        <div class="slider" id="<?= $id ?>">
        <?php while($d=mysqli_fetch_assoc($data)){ ?>
            <a href="detail_<?= $type ?>.php?id=<?= $d['id'] ?>" class="card">
                <img src="assets/img/<?= $d['gambar'] ?>" onerror="this.src='assets/img/default.jpg'">
                <div class="card-content">
                    <h5>
                        <?= $type=='kuliner' ? $d['nama_kuliner'] : ($type=='event' ? $d['judul'] : $d['nama']) ?>
                    </h5>
                    <p><?= substr($d['lokasi'] ?? $d['deskripsi'],0,60) ?>...</p>
                </div>
            </a>
        <?php } ?>
        </div>
    </div>
</section>
<?php } ?>

<?php
sliderSection("Destinasi Unggulan",$wisata,"wisata","wisata");
sliderSection("Kuliner Khas",$kuliner,"kuliner","kuliner");
sliderSection("Event Nganjuk",$event,"event","event");
?>

<script>
// NAV BUTTON
function slideLeft(id){
    document.getElementById(id).scrollBy({ left:-350, behavior:'smooth' });
}
function slideRight(id){
    document.getElementById(id).scrollBy({ left:350, behavior:'smooth' });
}

// AUTO SLIDE
function autoSlide(id){
    let el = document.getElementById(id);
    setInterval(()=>{
        el.scrollBy({left:300, behavior:'smooth'});
    },4000);
}
autoSlide('wisata');
autoSlide('kuliner');
autoSlide('event');

// DRAG
document.querySelectorAll('.slider').forEach(slider=>{
    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', e=>{
        isDown = true;
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });

    slider.addEventListener('mouseleave', ()=> isDown=false);
    slider.addEventListener('mouseup', ()=> isDown=false);

    slider.addEventListener('mousemove', e=>{
        if(!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 2;
        slider.scrollLeft = scrollLeft - walk;
    });
});
</script>

</body>
</html>