// Cart Fix Script - Connects the "V košarico" buttons to cart functionality
document.addEventListener('DOMContentLoaded', function(){
  function parsePrice(text){
    const m = text && text.match(/([\d.,]+)\s*€/);
    return m ? parseFloat(m[1].replace(/\./g,'').replace(',', '.')) : 0;
  }
  
  // Add unique identifiers to each product
  document.querySelectorAll('.product-card, .group').forEach((card, index) => {
    if (!card.hasAttribute('data-product-id')) {
      card.setAttribute('data-product-id', 'product-' + (index + 1));
    }
  });
  
  document.addEventListener('click', function(e){
    const btn = e.target.closest('button');
    if (!btn || !/V košarico/i.test(btn.textContent)) return;
    e.preventDefault();
    
    // Find the exact product container this button belongs to
    const card = btn.closest('.product-card, .group');
    if (!card) {
      console.log('No product card found');
      return;
    }
    
    const nameEl = card.querySelector('h3');
    const priceEl = card.querySelector('p[class*="amber-700"], p[class*="font-bold"]') || 
                   Array.from(card.querySelectorAll('p')).find(p => /€/.test(p.textContent));
    
    const name = nameEl ? nameEl.textContent.trim() : 'Izdelek';
    const price = parsePrice(priceEl ? priceEl.textContent : '0');
    
    // Use both name and container position for unique ID
    const productId = card.getAttribute('data-product-id') || 'unknown';
    const id = Math.abs((name + productId).split('').reduce((a,b)=>{a=((a<<5)-a)+b.charCodeAt(0);return a&a},0));
    
    console.log('Adding to cart:', {id, name, price, productId});
    
    addToCart(id, name, price);
    updateCartCount();
    
    // Visual feedback
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i data-feather="check" class="mr-1 w-4 h-4"></i> Dodano!';
    btn.style.backgroundColor = '#10b981';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.style.backgroundColor = '';
        btn.disabled = false;
        feather.replace();
    }, 2000);
  });
});