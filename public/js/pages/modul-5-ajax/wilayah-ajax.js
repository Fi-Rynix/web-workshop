$(document).ready(function() {
    loadProvinsi();
    
    $('#selectProvinsi').on('change', function() {
        const provinsiId = $(this).val();
        resetKota();
        resetKecamatan();
        resetKelurahan();
        
        if(provinsiId) {
            loadKota(provinsiId);
        }
        updateWilayahTerpilih();
    });
    
    $('#selectKota').on('change', function() {
        const kotaId = $(this).val();
        resetKecamatan();
        resetKelurahan();
        
        if(kotaId) {
            loadKecamatan(kotaId);
        }
        updateWilayahTerpilih();
    });
    
    $('#selectKecamatan').on('change', function() {
        const kecamatanId = $(this).val();
        resetKelurahan();
        
        if(kecamatanId) {
            loadKelurahan(kecamatanId);
        }
        updateWilayahTerpilih();
    });
    
    $('#selectKelurahan').on('change', function() {
        updateWilayahTerpilih();
    });
});


function loadProvinsi() {
    $.ajax({
        url: '/api/get-provinsi',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let selectProvinsi = $('#selectProvinsi');
            selectProvinsi.empty();
            selectProvinsi.append('<option value="">Pilih Provinsi</option>');
            
            if (response.success && response.data) {
                response.data.forEach(function(provinsi) {
                    selectProvinsi.append(
                        '<option value="' + provinsi.idprovinsi + '">' +
                        provinsi.nama_provinsi +
                        '</option>'
                    );
                });
            } else {
                alert('Data provinsi tidak valid');
            }
        },
        error: function(xhr, status, error) {
            console.log('XHR:', xhr);
            console.log('Status:', status);
            console.log('Error:', error);
            alert('Gagal memuat data provinsi');
        }
    });
}


function loadKota(provinsiId) {
    if(!provinsiId) {
        resetKota();
        return;
    }
    
    $.ajax({
        url: '/api/get-kota',
        type: 'GET',
        data: { provinsi_id: provinsiId },
        dataType: 'json',
        success: function(response) {
            let selectKota = $('#selectKota');
            selectKota.empty();
            selectKota.append('<option value="">Pilih Kota</option>');
            
            if(response.success && response.data) {
                response.data.forEach(function(kota) {
                    selectKota.append(
                        '<option value="' + kota.idkota + '">' +
                        kota.nama_kota + '</option>'
                    );
                });
                selectKota.prop('disabled', false);
            } else {
                selectKota.prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading kota:', error);
            resetKota();
        }
    });
}


function loadKecamatan(kotaId) {
    if(!kotaId) {
        resetKecamatan();
        return;
    }
    
    $.ajax({
        url: '/api/get-kecamatan',
        type: 'GET',
        data: { kota_id: kotaId },
        dataType: 'json',
        success: function(response) {
            let selectKecamatan = $('#selectKecamatan');
            selectKecamatan.empty();
            selectKecamatan.append('<option value="">Pilih Kecamatan</option>');
            
            if(response.success && response.data) {
                response.data.forEach(function(kecamatan) {
                    selectKecamatan.append(
                        '<option value="' + kecamatan.idkecamatan + '">' +
                        kecamatan.nama_kecamatan + '</option>'
                    );
                });
                selectKecamatan.prop('disabled', false);
            } else {
                selectKecamatan.prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading kecamatan:', error);
            resetKecamatan();
        }
    });
}


function loadKelurahan(kecamatanId) {
    if(!kecamatanId) {
        resetKelurahan();
        return;
    }
    
    $.ajax({
        url: '/api/get-kelurahan',
        type: 'GET',
        data: { kecamatan_id: kecamatanId },
        dataType: 'json',
        success: function(response) {
            let selectKelurahan = $('#selectKelurahan');
            selectKelurahan.empty();
            selectKelurahan.append('<option value="">Pilih Kelurahan</option>');
            
            if(response.success && response.data) {
                response.data.forEach(function(kelurahan) {
                    selectKelurahan.append(
                        '<option value="' + kelurahan.idkelurahan + '">' +
                        kelurahan.nama_kelurahan + '</option>'
                    );
                });
                selectKelurahan.prop('disabled', false);
            } else {
                selectKelurahan.prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading kelurahan:', error);
            resetKelurahan();
        }
    });
}


function updateWilayahTerpilih() {
    const provinsi = $('#selectProvinsi option:selected').text();
    const kota = $('#selectKota option:selected').text();
    const kecamatan = $('#selectKecamatan option:selected').text();
    const kelurahan = $('#selectKelurahan option:selected').text();
    
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
    
    $('#wilayahTerpilih').val(result);
}


function resetKota() {
    $('#selectKota').empty();
    $('#selectKota').append('<option value="">Pilih Kota</option>');
    $('#selectKota').prop('disabled', true);
    $('#kotaTerpilih').val('');
}


function resetKecamatan() {
    $('#selectKecamatan').empty();
    $('#selectKecamatan').append('<option value="">Pilih Kecamatan</option>');
    $('#selectKecamatan').prop('disabled', true);
    $('#kecamatanTerpilih').val('');
}


function resetKelurahan() {
    $('#selectKelurahan').empty();
    $('#selectKelurahan').append('<option value="">Pilih Kelurahan</option>');
    $('#selectKelurahan').prop('disabled', true);
    $('#kelurahanTerpilih').val('');
}
