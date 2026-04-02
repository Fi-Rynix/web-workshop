document.addEventListener('DOMContentLoaded', function() {
    loadProvinsi();
    
    document.getElementById('selectProvinsi').addEventListener('change', function() {
        const provinsiId = this.value;
        resetKota();
        resetKecamatan();
        resetKelurahan();
        
        if(provinsiId) {
            loadKota(provinsiId);
        }
        updateWilayahTerpilih();
    });
    
    document.getElementById('selectKota').addEventListener('change', function() {
        const kotaId = this.value;
        resetKecamatan();
        resetKelurahan();
        
        if(kotaId) {
            loadKecamatan(kotaId);
        }
        updateWilayahTerpilih();
    });
    
    document.getElementById('selectKecamatan').addEventListener('change', function() {
        const kecamatanId = this.value;
        resetKelurahan();
        
        if(kecamatanId) {
            loadKelurahan(kecamatanId);
        }
        updateWilayahTerpilih();
    });
    
    document.getElementById('selectKelurahan').addEventListener('change', function() {
        updateWilayahTerpilih();
    });
});


function loadProvinsi() {
    axios.get('/api/get-provinsi')
        .then(function(response) {
            const selectProvinsi = document.getElementById('selectProvinsi');
            selectProvinsi.innerHTML = '<option value="">Pilih Provinsi</option>';
            
            if (response.data.success && response.data.data) {
                response.data.data.forEach(function(provinsi) {
                    const option = document.createElement('option');
                    option.value = provinsi.idprovinsi;
                    option.textContent = provinsi.nama_provinsi;
                    selectProvinsi.appendChild(option);
                });
            } else {
                alert('Data provinsi tidak valid');
            }
        })
        .catch(function(error) {
            console.error('Error loading provinsi:', error);
            alert('Gagal memuat data provinsi');
        });
}


function loadKota(provinsiId) {
    if(!provinsiId) {
        resetKota();
        return;
    }
    
    axios.get('/api/get-kota', {
        params: { provinsi_id: provinsiId }
    })
        .then(function(response) {
            const selectKota = document.getElementById('selectKota');
            selectKota.innerHTML = '<option value="">Pilih Kota</option>';
            
            if(response.data.success && response.data.data) {
                response.data.data.forEach(function(kota) {
                    const option = document.createElement('option');
                    option.value = kota.idkota;
                    option.textContent = kota.nama_kota;
                    selectKota.appendChild(option);
                });
                selectKota.disabled = false;
            } else {
                selectKota.disabled = true;
            }
        })
        .catch(function(error) {
            console.error('Error loading kota:', error);
            resetKota();
        });
}


function loadKecamatan(kotaId) {
    if(!kotaId) {
        resetKecamatan();
        return;
    }
    
    axios.get('/api/get-kecamatan', {
        params: { kota_id: kotaId }
    })
        .then(function(response) {
            const selectKecamatan = document.getElementById('selectKecamatan');
            selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            if(response.data.success && response.data.data) {
                response.data.data.forEach(function(kecamatan) {
                    const option = document.createElement('option');
                    option.value = kecamatan.idkecamatan;
                    option.textContent = kecamatan.nama_kecamatan;
                    selectKecamatan.appendChild(option);
                });
                selectKecamatan.disabled = false;
            } else {
                selectKecamatan.disabled = true;
            }
        })
        .catch(function(error) {
            console.error('Error loading kecamatan:', error);
            resetKecamatan();
        });
}


function loadKelurahan(kecamatanId) {
    if(!kecamatanId) {
        resetKelurahan();
        return;
    }
    
    axios.get('/api/get-kelurahan', {
        params: { kecamatan_id: kecamatanId }
    })
        .then(function(response) {
            const selectKelurahan = document.getElementById('selectKelurahan');
            selectKelurahan.innerHTML = '<option value="">Pilih Kelurahan</option>';
            
            if(response.data.success && response.data.data) {
                response.data.data.forEach(function(kelurahan) {
                    const option = document.createElement('option');
                    option.value = kelurahan.idkelurahan;
                    option.textContent = kelurahan.nama_kelurahan;
                    selectKelurahan.appendChild(option);
                });
                selectKelurahan.disabled = false;
            } else {
                selectKelurahan.disabled = true;
            }
        })
        .catch(function(error) {
            console.error('Error loading kelurahan:', error);
            resetKelurahan();
        });
}


function updateWilayahTerpilih() {
    const selectProvinsi = document.getElementById('selectProvinsi');
    const selectKota = document.getElementById('selectKota');
    const selectKecamatan = document.getElementById('selectKecamatan');
    const selectKelurahan = document.getElementById('selectKelurahan');
    
    const provinsi = selectProvinsi.options[selectProvinsi.selectedIndex].text;
    const kota = selectKota.options[selectKota.selectedIndex].text;
    const kecamatan = selectKecamatan.options[selectKecamatan.selectedIndex].text;
    const kelurahan = selectKelurahan.options[selectKelurahan.selectedIndex].text;
    
    let result = '';
    
    if(provinsi !== 'Pilih Provinsi') {
        result = provinsi;
    }
    
    if(result && kota !== 'Pilih Kota') {
        result += ' -> ' + kota;
    }
    
    if(result && kecamatan !== 'Pilih Kecamatan') {
        result += ' -> ' + kecamatan;
    }
    
    if(result && kelurahan !== 'Pilih Kelurahan') {
        result += ' -> ' + kelurahan;
    }
    
    document.getElementById('wilayahTerpilih').value = result;
}


function resetKota() {
    const selectKota = document.getElementById('selectKota');
    selectKota.innerHTML = '<option value="">Pilih Kota</option>';
    selectKota.disabled = true;
    document.getElementById('wilayahTerpilih').value = '';
}


function resetKecamatan() {
    const selectKecamatan = document.getElementById('selectKecamatan');
    selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';
    selectKecamatan.disabled = true;
    document.getElementById('wilayahTerpilih').value = '';
}


function resetKelurahan() {
    const selectKelurahan = document.getElementById('selectKelurahan');
    selectKelurahan.innerHTML = '<option value="">Pilih Kelurahan</option>';
    selectKelurahan.disabled = true;
    document.getElementById('wilayahTerpilih').value = '';
}
