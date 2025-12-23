from pathlib import Path

sections = {
    'app/Http/Controllers/CouponController.php': (1, 200),
    'app/Models/Coupon.php': (1, 200),
    'app/Http/Requests/CouponRequest.php': (1, 200),
    'routes/web.php': (1, 220),
    'resources/views/panel/coupons/index.blade.php': (1, 400),
    'resources/views/panel/coupons/form.blade.php': (1, 400),
    'resources/lang/en/locale.php': (190, 260),
    'resources/lang/es/locale.php': (50, 140),
}

for path, (start, end) in sections.items():
    content = Path(path).read_text().splitlines()
    print(f"== {path} ==")
    for idx in range(max(0, start - 1), min(len(content), end)):
        print(f"{idx + 1:04d}: {content[idx]}")
    print()
