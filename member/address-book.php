<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("
                INSERT INTO user_addresses (user_id, label, recipient_name, phone, address,
                                           latitude, longitude, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $_POST['label'],
                $_POST['recipient_name'],
                $_POST['phone'],
                $_POST['address'],
                $_POST['latitude'] ?? null,
                $_POST['longitude'] ?? null,
                isset($_POST['is_default']) ? 1 : 0
            ]);

            if (isset($_POST['is_default'])) {
                $newId = $pdo->lastInsertId();
                $pdo->exec("UPDATE user_addresses SET is_default = 0 WHERE user_id = $userId AND id != $newId");
            }

            $_SESSION['success'] = 'Address added successfully!';
            header('Location: /member/address-book.php');
            exit;
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['address_id'], $userId]);
            $_SESSION['success'] = 'Address deleted!';
            header('Location: /member/address-book.php');
            exit;
        } elseif ($action === 'set_default') {
            $pdo->exec("UPDATE user_addresses SET is_default = 0 WHERE user_id = $userId");
            $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['address_id'], $userId]);
            $_SESSION['success'] = 'Default address updated!';
            header('Location: /member/address-book.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Get addresses
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();

$page_title = 'Address Book - Dorve.id';
include __DIR__ . '/../includes/header.php';
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        --success-gradient: linear-gradient(135deg, #10B981 0%, #059669 100%);
        --danger-color: #EF4444;
        --text-primary: #1F2937;
        --text-secondary: #6B7280;
        --border-color: #E5E7EB;
        --bg-light: #F9FAFB;
    }

    .member-content {
        flex: 1;
        min-width: 0;
    }

    .member-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 42px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #1A1A1A 0%, #667EEA 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
    }

    .page-description {
        color: var(--text-secondary);
        margin-bottom: 36px;
        font-size: 16px;
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success {
        background: #D1FAE5;
        color: #065F46;
        border: 1px solid #10B981;
    }

    .alert-error {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #EF4444;
    }

    .btn-add {
        padding: 16px 32px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        margin-bottom: 32px;
        font-size: 15px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-add:active {
        transform: translateY(0);
    }

    .address-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 24px;
    }

    .address-card {
        background: white;
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border: 2px solid var(--border-color);
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .address-card:hover {
        border-color: #667EEA;
        box-shadow: 0 8px 28px rgba(102, 126, 234, 0.15);
        transform: translateY(-4px);
    }

    .address-card.default {
        border-color: #10B981;
        background: linear-gradient(to bottom, #ECFDF5 0%, white 40%);
    }

    .default-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: var(--success-gradient);
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .address-label {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 16px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .address-details {
        font-size: 14px;
        color: #374151;
        line-height: 1.9;
    }

    .address-details > div {
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .address-details strong {
        font-weight: 600;
        color: var(--text-primary);
    }

    .address-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .btn {
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
    }

    .btn-default {
        background: var(--success-gradient);
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
    }
    .btn-default:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-delete {
        background: var(--danger-color);
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }
    .btn-delete:hover {
        background: #DC2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(8px);
        overflow-y: auto;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background: white;
        border-radius: 24px;
        max-width: 950px;
        width: 92%;
        margin: 40px auto;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal h2 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        font-family: 'Playfair Display', serif;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .modal-subtitle {
        color: var(--text-secondary);
        margin-bottom: 32px;
        font-size: 15px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .form-group input, .form-group textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s;
        background: var(--bg-light);
    }

    .form-group input:focus, .form-group textarea:focus {
        outline: none;
        border-color: #667EEA;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    textarea {
        min-height: 110px;
        resize: vertical;
        font-family: inherit;
    }

    #map {
        width: 100%;
        height: 450px;
        border-radius: 16px;
        border: 2px solid var(--border-color);
        margin: 20px 0;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    .map-hint {
        font-size: 14px;
        color: var(--text-secondary);
        padding: 16px 20px;
        background: #FEF3C7;
        border-radius: 12px;
        margin-bottom: 20px;
        border-left: 4px solid #F59E0B;
        line-height: 1.6;
    }

    .btn-submit {
        width: 100%;
        padding: 18px;
        background: var(--success-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(16, 185, 129, 0.4);
    }

    .btn-cancel {
        background: #6B7280;
        box-shadow: 0 4px 16px rgba(107, 114, 128, 0.3);
    }

    .btn-cancel:hover {
        background: #4B5563;
    }

    .empty-state {
        grid-column: 1/-1;
        text-align: center;
        padding: 80px 40px;
        background: var(--bg-light);
        border-radius: 20px;
        border: 2px dashed var(--border-color);
    }

    .empty-state-icon {
        font-size: 80px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 12px;
        color: var(--text-primary);
    }

    .empty-state p {
        color: var(--text-secondary);
    }

    @media (max-width: 968px) {
        .member-content h1 {
            font-size: 32px;
        }

        .address-grid {
            grid-template-columns: 1fr;
        }

        .modal-content {
            padding: 28px 24px;
            width: 95%;
        }

        .modal h2 {
            font-size: 26px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        #map {
            height: 350px;
        }
    }

    @media (max-width: 480px) {
        .member-content h1 {
            font-size: 26px;
        }

        .btn-add {
            width: 100%;
            justify-content: center;
        }

        .address-card {
            padding: 20px;
        }

        .address-actions {
            flex-direction: column;
        }

        .address-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="member-layout">
    <?php include __DIR__ . '/../includes/member-sidebar.php'; ?>

    <div class="member-content">
        <h1>📍 Address Book</h1>
        <p class="page-description">Manage your shipping addresses for fast checkout</p>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <button onclick="openModal()" class="btn-add">
            <span style="font-size: 18px;">➕</span> Add New Address
        </button>

        <div class="address-grid">
            <?php if (empty($addresses)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📍</div>
                    <h3>No Addresses Yet</h3>
                    <p style="margin-top: 8px;">Add your first shipping address to speed up checkout</p>
                </div>
            <?php else: ?>
                <?php foreach ($addresses as $addr): ?>
                <div class="address-card <?= $addr['is_default'] ? 'default' : '' ?>">
                    <?php if ($addr['is_default']): ?>
                        <div class="default-badge">✓ DEFAULT</div>
                    <?php endif; ?>

                    <div class="address-label">
                        <span style="font-size: 20px;">🏠</span>
                        <?= htmlspecialchars($addr['label']) ?>
                    </div>

                    <div class="address-details">
                        <div>
                            <span>👤</span>
                            <strong><?= htmlspecialchars($addr['recipient_name']) ?></strong>
                        </div>
                        <div>
                            <span>📱</span>
                            <?= htmlspecialchars($addr['phone']) ?>
                        </div>
                        <div>
                            <span>📍</span>
                            <?= nl2br(htmlspecialchars($addr['address'])) ?>
                        </div>
                        <?php if ($addr['latitude'] && $addr['longitude']): ?>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
                                <span>📌</span>
                                Lat: <?= $addr['latitude'] ?>, Long: <?= $addr['longitude'] ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="address-actions">
                        <?php if (!$addr['is_default']): ?>
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="action" value="set_default">
                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                            <button type="submit" class="btn btn-default" style="width: 100%;">
                                Set as Default
                            </button>
                        </form>
                        <?php endif; ?>

                        <form method="POST" onsubmit="return confirm('Delete this address?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                            <button type="submit" class="btn btn-delete">
                                🗑️ Delete
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal" id="addressModal">
    <div class="modal-content">
        <h2>📍 Add New Address</h2>
        <p class="modal-subtitle">Pin your exact location for accurate delivery</p>

        <form method="POST" id="addressForm">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <div class="form-group">
                <label>Address Label *</label>
                <input type="text" name="label" placeholder="e.g., Home, Office, Mom's House" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Recipient Name *</label>
                    <input type="text" name="recipient_name" required>
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" placeholder="08123456789" required>
                </div>
            </div>

            <div class="form-group">
                <label>🔍 Search Location</label>
                <input type="text" id="autocomplete" placeholder="Type address to search..." autocomplete="off">
            </div>

            <div class="map-hint">
                📍 <strong>Search or click on the map</strong> to select your exact location. You can drag the marker to fine-tune the position.
            </div>

            <div id="map"></div>

            <div class="form-group">
                <label>Full Address *</label>
                <textarea name="address" id="addressField" placeholder="Street name, building number, area, postal code..." required></textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_default" style="width: auto; cursor: pointer;">
                    <span>Set as default shipping address</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn-submit" style="flex: 2;">
                    ✓ Save Address
                </button>
                <button type="button" onclick="closeModal()" class="btn-submit btn-cancel" style="flex: 1;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://js.api.here.com/v3/3.1/mapsjs-core.js"></script>
<script src="https://js.api.here.com/v3/3.1/mapsjs-service.js"></script>
<script src="https://js.api.here.com/v3/3.1/mapsjs-ui.js"></script>
<script src="https://js.api.here.com/v3/3.1/mapsjs-mapevents.js"></script>

<script>
const HERE_API_KEY = 'gvL9FlCOjPwyh0aSaMG7fyP_wr8mMXs0UqERooLBXrs';

let map, marker, platform, autocompleteTimeout;

function openModal() {
    document.getElementById('addressModal').style.display = 'block';
    document.body.style.overflow = 'hidden';

    if (!map) {
        setTimeout(initHereMap, 100);
    }
}

function closeModal() {
    document.getElementById('addressModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('addressForm').reset();
}

function initHereMap() {
    try {
        platform = new H.service.Platform({
            apikey: HERE_API_KEY
        });

        const defaultLayers = platform.createDefaultLayers();
        const defaultPos = { lat: -6.2088, lng: 106.8456 };

        map = new H.Map(
            document.getElementById('map'),
            defaultLayers.vector.normal.map,
            {
                center: defaultPos,
                zoom: 15,
                pixelRatio: window.devicePixelRatio || 1
            }
        );

        window.addEventListener('resize', () => map.getViewPort().resize());

        const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
        const ui = H.ui.UI.createDefault(map, defaultLayers);

        marker = new H.map.Marker(defaultPos, {
            volatility: true
        });
        marker.draggable = true;
        map.addObject(marker);

        marker.addEventListener('dragend', function(ev) {
            const coord = map.screenToGeo(
                ev.currentPointer.viewportX,
                ev.currentPointer.viewportY
            );
            marker.setGeometry(coord);
            updateAddress(coord.lat, coord.lng);
        });

        map.addEventListener('tap', function(evt) {
            const coord = map.screenToGeo(
                evt.currentPointer.viewportX,
                evt.currentPointer.viewportY
            );
            marker.setGeometry(coord);
            map.setCenter(coord);
            updateAddress(coord.lat, coord.lng);
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                marker.setGeometry(pos);
                map.setCenter(pos);
                updateAddress(pos.lat, pos.lng);
            });
        }

        setupAutocomplete();

    } catch (error) {
        console.error('HERE Map initialization error:', error);
        document.getElementById('map').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #FEE2E2; flex-direction: column; padding: 20px; text-align: center;"><div style="font-size: 48px; margin-bottom: 16px;">❌</div><div style="font-size: 16px; font-weight: 600; color: #991B1B;">Map Error: ' + error.message + '</div></div>';
    }
}

function setupAutocomplete() {
    const input = document.getElementById('autocomplete');

    input.addEventListener('input', function() {
        clearTimeout(autocompleteTimeout);
        const query = this.value.trim();

        if (query.length < 3) return;

        autocompleteTimeout = setTimeout(() => {
            fetch(`https://autocomplete.search.hereapi.com/v1/autocomplete?q=${encodeURIComponent(query)}&at=-6.2088,106.8456&limit=5&apiKey=${HERE_API_KEY}`)
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        const firstResult = data.items[0];
                        if (firstResult.position) {
                            const pos = {
                                lat: firstResult.position.lat,
                                lng: firstResult.position.lng
                            };
                            marker.setGeometry(pos);
                            map.setCenter(pos);
                            map.setZoom(17);
                            updateAddress(pos.lat, pos.lng);
                        }
                    }
                })
                .catch(err => console.error('Autocomplete error:', err));
        }, 500);
    });
}

function updateAddress(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);

    fetch(`https://revgeocode.search.hereapi.com/v1/revgeocode?at=${lat},${lng}&apiKey=${HERE_API_KEY}`)
        .then(response => response.json())
        .then(data => {
            if (data.items && data.items.length > 0) {
                const address = data.items[0].address;
                const fullAddress = address.label ||
                    [address.street, address.district, address.city, address.postalCode, address.countryName]
                        .filter(Boolean)
                        .join(', ');
                document.getElementById('addressField').value = fullAddress;
            }
        })
        .catch(err => console.error('Reverse geocoding error:', err));
}

document.getElementById('addressModal').addEventListener('click', (e) => {
    if (e.target.id === 'addressModal') closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
