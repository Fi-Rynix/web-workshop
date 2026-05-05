function initWilayahDropdown(config) {
    const cfg = {
        selectIds: {
            provinsi: 'selectProvinsi',
            kota: 'selectKota',
            kecamatan: 'selectKecamatan',
            kelurahan: 'selectKelurahan'
        },
        onChange: null,
        ...config
    };

    const selectProvinsi = document.getElementById(cfg.selectIds.provinsi);
    const selectKota = document.getElementById(cfg.selectIds.kota);
    const selectKecamatan = document.getElementById(cfg.selectIds.kecamatan);
    const selectKelurahan = document.getElementById(cfg.selectIds.kelurahan);

    let currentData = {};

    if (!selectProvinsi) {
        console.error('initWilayahDropdown: Element provinsi tidak ditemukan');
        return null;
    }

    selectProvinsi.addEventListener('change', function() {
        const provinsiId = this.options[this.selectedIndex].getAttribute('data-id') || this.value;
        const provinsiNama = this.options[this.selectedIndex].text;

        resetKota();
        resetKecamatan();
        resetKelurahan();

        currentData = {
            provinsiId: provinsiId,
            provinsiNama: provinsiId ? provinsiNama : ''
        };

        if(provinsiId) {
            loadKota(provinsiId);
        }

        triggerOnChange();
    });

    selectKota.addEventListener('change', function() {
        const kotaId = this.options[this.selectedIndex].getAttribute('data-id') || this.value;
        const kotaNama = this.options[this.selectedIndex].text;

        resetKecamatan();
        resetKelurahan();

        currentData.kotaId = kotaId;
        currentData.kotaNama = kotaId ? kotaNama : '';

        if(kotaId) {
            loadKecamatan(kotaId);
        }

        triggerOnChange();
    });

    selectKecamatan.addEventListener('change', function() {
        const kecamatanId = this.options[this.selectedIndex].getAttribute('data-id') || this.value;
        const kecamatanNama = this.options[this.selectedIndex].text;

        resetKelurahan();

        currentData.kecamatanId = kecamatanId;
        currentData.kecamatanNama = kecamatanId ? kecamatanNama : '';

        if(kecamatanId) {
            loadKelurahan(kecamatanId);
        }

        triggerOnChange();
    });

    selectKelurahan.addEventListener('change', function() {
        const kelurahanId = this.options[this.selectedIndex].getAttribute('data-id') || this.value;
        const kelurahanNama = this.options[this.selectedIndex].text;

        currentData.kelurahanId = kelurahanId;
        currentData.kelurahanNama = kelurahanId ? kelurahanNama : '';

        triggerOnChange();
    });

    function triggerOnChange() {
        if (typeof cfg.onChange === 'function') {
            cfg.onChange({ ...currentData });
        }
    }

    function loadProvinsi() {
        axios.get('/api/get-provinsi')
            .then(function(response) {
                selectProvinsi.innerHTML = '<option value="">Pilih Provinsi</option>';

                if (response.data.success && response.data.data) {
                    response.data.data.forEach(function(provinsi) {
                        const option = document.createElement('option');
                        option.value = provinsi.nama_provinsi;
                        option.setAttribute('data-id', provinsi.idprovinsi);
                        option.textContent = provinsi.nama_provinsi;
                        selectProvinsi.appendChild(option);
                    });
                }
            })
            .catch(function(error) {
                console.error('Error loading provinsi:', error);
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
                selectKota.innerHTML = '<option value="">Pilih Kota</option>';

                if(response.data.success && response.data.data) {
                    response.data.data.forEach(function(kota) {
                        const option = document.createElement('option');
                        option.value = kota.nama_kota;
                        option.setAttribute('data-id', kota.idkota);
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
                selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';

                if(response.data.success && response.data.data) {
                    response.data.data.forEach(function(kecamatan) {
                        const option = document.createElement('option');
                        option.value = kecamatan.nama_kecamatan;
                        option.setAttribute('data-id', kecamatan.idkecamatan);
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
                selectKelurahan.innerHTML = '<option value="">Pilih Kelurahan</option>';

                if(response.data.success && response.data.data) {
                    response.data.data.forEach(function(kelurahan) {
                        const option = document.createElement('option');
                        option.value = kelurahan.nama_kelurahan;
                        option.setAttribute('data-id', kelurahan.idkelurahan);
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

    function resetKota() {
        selectKota.innerHTML = '<option value="">Pilih Kota</option>';
        selectKota.disabled = true;
    }

    function resetKecamatan() {
        selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';
        selectKecamatan.disabled = true;
    }

    function resetKelurahan() {
        selectKelurahan.innerHTML = '<option value="">Pilih Kelurahan</option>';
        selectKelurahan.disabled = true;
    }

    return {
        loadProvinsi: loadProvinsi,
        getSelectedValues: function() {
            return { ...currentData };
        },
        updateDropdownValuesToNama: function() {
            // Enable semua dropdown sebelum submit agar value-nya dikirim
            // (dropdown yang disabled tidak dikirim dalam form submission)
            selectKota.disabled = false;
            selectKecamatan.disabled = false;
            selectKelurahan.disabled = false;
        },
        reset: function() {
            resetKota();
            resetKecamatan();
            resetKelurahan();
            selectProvinsi.value = '';
            currentData = {};
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('selectProvinsi') &&
        document.getElementById('selectKota') &&
        document.getElementById('selectKecamatan') &&
        document.getElementById('selectKelurahan')) {

        if (!window.wilayahDropdown) {
            const wilayah = initWilayahDropdown({
                onChange: function(data) {
                    const wilayahTerpilih = document.getElementById('wilayahTerpilih');
                    if (wilayahTerpilih) {
                        let result = '';
                        if (data.provinsiNama) result = data.provinsiNama;
                        if (data.kotaNama) result += ' -> ' + data.kotaNama;
                        if (data.kecamatanNama) result += ' -> ' + data.kecamatanNama;
                        if (data.kelurahanNama) result += ' -> ' + data.kelurahanNama;
                        wilayahTerpilih.value = result;
                    }
                }
            });

            if (wilayah) {
                wilayah.loadProvinsi();
            }
        }
    }
});
