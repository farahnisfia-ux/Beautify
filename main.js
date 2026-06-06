// ── SIDEBAR (admin) ──
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
document.addEventListener('click',function(e){
    const sb=document.getElementById('adminSidebar');
    const hb=document.querySelector('.admin-hamburger');
    if(window.innerWidth<=900&&sb&&sb.classList.contains('open'))
        if(!sb.contains(e.target)&&hb&&!hb.contains(e.target)) sb.classList.remove('open');
});

// ── PROFILE DROPDOWN ──
function toggleProfileDD(){document.getElementById('profDD').classList.toggle('open');}
document.addEventListener('click',function(e){
    const w=document.getElementById('profWrap');
    const dd=document.getElementById('profDD');
    if(w&&dd&&!w.contains(e.target)) dd.classList.remove('open');
});

// ── MODAL ──
function openModal(id){document.getElementById(id).classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
document.addEventListener('click',function(e){if(e.target.classList.contains('modal-overlay'))e.target.classList.remove('show');});
document.addEventListener('keydown',function(e){if(e.key==='Escape')document.querySelectorAll('.modal-overlay.show').forEach(m=>m.classList.remove('show'));});

// ── IMAGE PREVIEW ──
function previewImg(input,id){
    const p=document.getElementById(id);if(!p)return;
    if(input.files&&input.files[0]){const r=new FileReader();r.onload=e=>{p.src=e.target.result;p.classList.add('show');};r.readAsDataURL(input.files[0]);}
}

// ── AUTO HIDE ALERT ──
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('.alert').forEach(a=>{
        setTimeout(()=>{a.style.transition='opacity .4s,transform .4s';a.style.opacity='0';a.style.transform='translateY(-8px)';setTimeout(()=>a.remove(),400);},4500);
    });
});

// ── CONFIRM DELETE ──
function confirmDel(url,name){
    document.getElementById('delName').textContent=name;
    document.getElementById('delBtn').onclick=()=>window.location.href=url;
    openModal('modalDel');
}

// ── FORMAT RUPIAH ──
function fmtRp(n){return'Rp '+parseInt(n).toLocaleString('id-ID');}

// ── CART ──
let cart=JSON.parse(sessionStorage.getItem('bfCart')||'[]');
function saveCart(){sessionStorage.setItem('bfCart',JSON.stringify(cart));}
function updateBadge(){const b=document.getElementById('cartBadge');if(b)b.textContent=cart.reduce((s,c)=>s+c.qty,0);}

function openCart(){document.getElementById('cartSide').classList.add('open');document.getElementById('cartOverlay').classList.add('open');renderCart();}
function closeCart(){document.getElementById('cartSide').classList.remove('open');document.getElementById('cartOverlay').classList.remove('open');}

function addToCart(id,name,price,img){
    const ex=cart.find(c=>c.id==id);
    if(ex)ex.qty++;else cart.push({id,name,price:parseInt(price),img,qty:1});
    saveCart();updateBadge();renderCart();openCart();
    const btn=document.querySelector(`.cart-qbtn[data-id="${id}"]`);
    if(btn){btn.innerHTML='✓';setTimeout(()=>btn.innerHTML='<i class="fas fa-cart-plus"></i>',1200);}
}
function changeQty(id,d){
    const item=cart.find(c=>c.id==id);if(!item)return;
    item.qty+=d;if(item.qty<=0)cart=cart.filter(c=>c.id!=id);
    saveCart();updateBadge();renderCart();
}
function removeFromCart(id){cart=cart.filter(c=>c.id!=id);saveCart();updateBadge();renderCart();}

function renderCart(){
    const list=document.getElementById('cartItems');
    const foot=document.getElementById('cartFoot');
    if(!list)return;
    if(cart.length===0){
        list.innerHTML='<div class="cart-empty"><i class="fas fa-shopping-bag"></i><p>Keranjang masih kosong</p><small>Yuk tambahkan produk!</small></div>';
        if(foot)foot.style.display='none';return;
    }
    if(foot){
        foot.style.display='block';
        document.getElementById('cartCount').textContent=cart.reduce((s,c)=>s+c.qty,0)+' produk';
        document.getElementById('cartTotal').textContent=fmtRp(cart.reduce((s,c)=>s+c.price*c.qty,0));
    }
    list.innerHTML=cart.map(item=>`
    <div class="cart-row">
      <img src="${item.img||'assets/img/no-img.png'}" alt="${item.name}">
      <div class="cart-row-info">
        <div class="cart-row-name">${item.name}</div>
        <div class="cart-row-price">${fmtRp(item.price)}</div>
        <div class="qty-ctrl">
          <button onclick="changeQty(${item.id},-1)">−</button>
          <span class="qnum">${item.qty}</span>
          <button onclick="changeQty(${item.id},+1)">+</button>
        </div>
      </div>
      <button class="cart-del-btn" onclick="removeFromCart(${item.id})">✕</button>
    </div>`).join('');
}

// ── SEARCH FILTER (produk grid) ──
function filterProds(){
    const q=document.getElementById('searchProd')?document.getElementById('searchProd').value.toLowerCase():'';
    document.querySelectorAll('.prod-card').forEach(c=>{
        c.style.display=c.dataset.nama.includes(q)?'':'none';
    });
}

updateBadge();
