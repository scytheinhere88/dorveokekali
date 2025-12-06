<!-- Tracking Modal (Include this in pages that need tracking) -->
<div id="trackingModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 99999; padding: 20px; overflow-y: auto;" onclick="if(event.target === this) closeTrackingModal()">
    <div style="max-width: 700px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <!-- Modal Header -->
        <div style="padding: 24px 30px; border-bottom: 2px solid #F3F4F6; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-family: 'Playfair Display', serif; font-size: 24px;">📦 Track Paket</h2>
            <button onclick="closeTrackingModal()" style="width: 36px; height: 36px; border: none; background: #F3F4F6; border-radius: 50%; cursor: pointer; font-size: 20px; color: #6B7280;">&times;</button>
        </div>

        <!-- Modal Content -->
        <div id="trackingContent" style="padding: 30px;">
            <div style="text-align: center; padding: 40px; color: #9CA3AF;">
                <div style="font-size: 48px; margin-bottom: 16px;">🔄</div>
                <p>Loading tracking information...</p>
            </div>
        </div>
    </div>
</div>

<style>
.tracking-status-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 24px;
    border-radius: 12px;
    color: white;
    margin-bottom: 24px;
    text-align: center;
}
.tracking-status-icon {
    font-size: 48px;
    margin-bottom: 12px;
}
.tracking-status-text {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
}
.tracking-waybill {
    background: rgba(255,255,255,0.2);
    padding: 12px 20px;
    border-radius: 8px;
    margin-top: 16px;
    display: inline-block;
}
.tracking-waybill-number {
    font-family: 'Courier New', monospace;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 1px;
}
.tracking-timeline {
    position: relative;
    padding-left: 40px;
    margin-top: 30px;
}
.tracking-timeline-item {
    position: relative;
    padding-bottom: 32px;
}
.tracking-timeline-item:last-child {
    padding-bottom: 0;
}
.tracking-timeline-item::before {
    content: '';
    position: absolute;
    left: -32px;
    top: 0;
    width: 3px;
    height: 100%;
    background: #E5E7EB;
}
.tracking-timeline-item:last-child::before {
    display: none;
}
.tracking-timeline-dot {
    position: absolute;
    left: -40px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    border: 4px solid #10B981;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.tracking-timeline-item.inactive .tracking-timeline-dot {
    border-color: #E5E7EB;
    background: #F9FAFB;
}
.tracking-timeline-title {
    font-weight: 600;
    margin-bottom: 6px;
    color: #1F2937;
}
.tracking-timeline-time {
    font-size: 13px;
    color: #6B7280;
}
.tracking-timeline-desc {
    font-size: 14px;
    color: #6B7280;
    margin-top: 4px;
}
.courier-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #F3F4F6;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 16px;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 20px;
}
.info-item {
    background: #F9FAFB;
    padding: 16px;
    border-radius: 8px;
}
.info-label {
    font-size: 12px;
    color: #6B7280;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.info-value {
    font-size: 16px;
    font-weight: 600;
    color: #1F2937;
}
.copy-btn {
    background: rgba(255,255,255,0.3);
    border: 1px solid rgba(255,255,255,0.5);
    color: white;
    padding: 6px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    margin-left: 12px;
    transition: all 0.2s;
}
.copy-btn:hover {
    background: rgba(255,255,255,0.4);
}
</style>

<script>
function openTrackingModal(orderId) {
    document.getElementById('trackingModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Load tracking data
    fetch('/api/tracking/get-status.php?order_id=' + orderId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderTrackingData(data);
            } else {
                document.getElementById('trackingContent').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; color: #EF4444; margin-bottom: 16px;">❌</div>
                        <p style="color: #6B7280;">${data.error || 'Failed to load tracking'}</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            document.getElementById('trackingContent').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; color: #EF4444; margin-bottom: 16px;">⚠️</div>
                    <p style="color: #6B7280;">Error: ${err.message}</p>
                </div>
            `;
        });
}

function closeTrackingModal() {
    document.getElementById('trackingModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function renderTrackingData(data) {
    const order = data.order;
    const history = data.tracking_history || [];
    
    let html = '';
    
    // Status Card
    html += `
        <div class="tracking-status-card" style="background: ${order.status_display.color};">
            <div class="tracking-status-icon">${order.status_display.icon}</div>
            <div class="tracking-status-text">${order.status_display.label}</div>
            <div style="font-size: 14px; opacity: 0.9;">Order: ${order.order_number}</div>
    `;
    
    if (order.waybill_id) {
        html += `
            <div class="tracking-waybill">
                <div style="font-size: 11px; opacity: 0.8; margin-bottom: 4px;">Nomor Resi</div>
                <div class="tracking-waybill-number">${order.waybill_id}</div>
                <button class="copy-btn" onclick="copyToClipboard('${order.waybill_id}')">Copy</button>
            </div>
        `;
    }
    
    html += `</div>`;
    
    // Courier Info
    if (order.courier.company) {
        html += `
            <div style="text-align: center; margin-bottom: 24px;">
                <span class="courier-badge">
                    🚚 <strong>${order.courier.company.toUpperCase()}</strong> - ${order.courier.service}
                </span>
            </div>
        `;
    }
    
    // Info Grid
    html += `<div class="info-grid">`;
    
    if (order.destination.city) {
        html += `
            <div class="info-item">
                <div class="info-label">Tujuan</div>
                <div class="info-value">${order.destination.city}, ${order.destination.province}</div>
            </div>
        `;
    }
    
    if (order.weight_kg > 0) {
        html += `
            <div class="info-item">
                <div class="info-label">Berat Paket</div>
                <div class="info-value">${order.weight_kg} kg</div>
            </div>
        `;
    }
    
    if (order.shipping_cost > 0) {
        html += `
            <div class="info-item">
                <div class="info-label">Ongkos Kirim</div>
                <div class="info-value">Rp ${parseInt(order.shipping_cost).toLocaleString('id-ID')}</div>
            </div>
        `;
    }
    
    if (order.delivery_date) {
        html += `
            <div class="info-item">
                <div class="info-label">Estimasi Tiba</div>
                <div class="info-value">${new Date(order.delivery_date).toLocaleDateString('id-ID')}</div>
            </div>
        `;
    }
    
    html += `</div>`;
    
    // Tracking History
    if (history.length > 0) {
        html += `
            <div style="margin-top: 32px;">
                <h3 style="font-size: 18px; margin-bottom: 20px; color: #1F2937;">Riwayat Pengiriman</h3>
                <div class="tracking-timeline">
        `;
        
        history.forEach((item, index) => {
            const isActive = index === 0;
            html += `
                <div class="tracking-timeline-item ${isActive ? '' : 'inactive'}">
                    <div class="tracking-timeline-dot"></div>
                    <div class="tracking-timeline-title">${item.note || item.status || 'Update'}</div>
                    <div class="tracking-timeline-time">
                        ${item.updated_at ? new Date(item.updated_at).toLocaleString('id-ID', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric', 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        }) : ''}
                    </div>
                    ${item.service_code ? `<div class="tracking-timeline-desc">Service: ${item.service_code}</div>` : ''}
                </div>
            `;
        });
        
        html += `</div></div>`;
    } else if (!data.has_tracking) {
        html += `
            <div style="text-align: center; padding: 32px; background: #F9FAFB; border-radius: 8px; margin-top: 24px;">
                <div style="font-size: 36px; margin-bottom: 12px;">📦</div>
                <p style="color: #6B7280; margin: 0;">Paket belum dipickup oleh kurir. Informasi tracking akan muncul setelah kurir mengambil paket.</p>
            </div>
        `;
    } else {
        html += `
            <div style="text-align: center; padding: 32px; background: #F9FAFB; border-radius: 8px; margin-top: 24px;">
                <div style="font-size: 36px; margin-bottom: 12px;">🔄</div>
                <p style="color: #6B7280; margin: 0;">Tracking information sedang diupdate...</p>
            </div>
        `;
    }
    
    // Update Note
    html += `
        <div style="margin-top: 24px; padding: 16px; background: #DBEAFE; border-radius: 8px; border-left: 4px solid #3B82F6;">
            <p style="margin: 0; font-size: 13px; color: #1E40AF;">
                <strong>ℹ️ Info:</strong> Tracking diupdate secara real-time dari sistem kurir. Jika ada pertanyaan, hubungi admin dengan <strong>Order ID: ${order.order_number}</strong>
            </p>
        </div>
    `;
    
    document.getElementById('trackingContent').innerHTML = html;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ Nomor resi berhasil dicopy!');
    }).catch(err => {
        alert('❌ Gagal copy: ' + err.message);
    });
}
</script>