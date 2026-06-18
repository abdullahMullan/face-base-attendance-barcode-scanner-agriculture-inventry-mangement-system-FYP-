<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <!-- Wheat on left -->
    <g transform="translate(20, 20)">
        <!-- Left wheat stalk -->
        <path d="M 15 80 Q 10 60 15 40" stroke="#D4AF37" stroke-width="3" fill="none" stroke-linecap="round"/>
        <!-- Left wheat grains -->
        <ellipse cx="10" cy="35" rx="3" ry="5" fill="#D4AF37" transform="rotate(-30 10 35)"/>
        <ellipse cx="8" cy="45" rx="3" ry="5" fill="#D4AF37" transform="rotate(-30 8 45)"/>
        <ellipse cx="12" cy="55" rx="3" ry="5" fill="#D4AF37" transform="rotate(-30 12 55)"/>
        <ellipse cx="18" cy="40" rx="3" ry="5" fill="#D4AF37" transform="rotate(30 18 40)"/>
        <ellipse cx="16" cy="50" rx="3" ry="5" fill="#D4AF37" transform="rotate(30 16 50)"/>
        <ellipse cx="20" cy="60" rx="3" ry="5" fill="#D4AF37" transform="rotate(30 20 60)"/>
    </g>
    
    <!-- Wheat on right -->
    <g transform="translate(145, 20)">
        <!-- Right wheat stalk -->
        <path d="M 15 80 Q 20 60 15 40" stroke="#D4AF37" stroke-width="3" fill="none" stroke-linecap="round"/>
        <!-- Right wheat grains -->
        <ellipse cx="20" cy="35" rx="3" ry="5" fill="#D4AF37" transform="rotate(30 20 35)"/>
        <ellipse cx="22" cy="45" rx="3" ry="5" fill="#D4AF37" transform="rotate(30 22 45)"/>
        <ellipse cx="18" cy="55" rx="3" ry="5" fill="#D4AF37" transform="rotate(30 18 55)"/>
        <ellipse cx="12" cy="40" rx="3" ry="5" fill="#D4AF37" transform="rotate(-30 12 40)"/>
        <ellipse cx="14" cy="50" rx="3" ry="5" fill="#D4AF37" transform="rotate(-30 14 50)"/>
        <ellipse cx="10" cy="60" rx="3" ry="5" fill="#D4AF37" transform="rotate(-30 10 60)"/>
    </g>
    
    <!-- Tractor body - main frame -->
    <rect x="45" y="75" width="70" height="40" rx="5" fill="#1a472a" stroke="#0f2818" stroke-width="2"/>
    
    <!-- Cabin -->
    <rect x="55" y="65" width="25" height="15" rx="3" fill="#0f2818" stroke="#0f2818" stroke-width="1"/>
    
    <!-- Tractor wheels - back (larger) -->
    <circle cx="65" cy="120" r="12" fill="#1a1a1a" stroke="#555" stroke-width="1"/>
    <circle cx="65" cy="120" r="9" fill="none" stroke="#333" stroke-width="1"/>
    
    <!-- Tractor wheels - front (smaller) -->
    <circle cx="105" cy="115" r="8" fill="#1a1a1a" stroke="#555" stroke-width="1"/>
    <circle cx="105" cy="115" r="6" fill="none" stroke="#333" stroke-width="1"/>
    
    <!-- Tractor exhaust -->
    <rect x="75" y="50" width="4" height="15" fill="#666"/>
    
    <!-- Tractor details -->
    <circle cx="60" cy="85" r="2" fill="#4CAF50"/>
    <circle cx="85" cy="85" r="2" fill="#4CAF50"/>
    
    <!-- Circle frame around everything -->
    <circle cx="100" cy="100" r="95" fill="none" stroke="#1a472a" stroke-width="2"/>
</svg>
