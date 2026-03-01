/**
 * nepal-data.js - Official Geographic Data of Nepal
 * Includes 7 Provinces and 77 Districts
 */

const nepalData = {
    provinces: [
        { id: 1, name: "Koshi Province" },
        { id: 2, name: "Madhesh Province" },
        { id: 3, name: "Bagmati Province" },
        { id: 4, name: "Gandaki Province" },
        { id: 5, name: "Lumbini Province" },
        { id: 6, name: "Karnali Province" },
        { id: 7, name: "Sudurpashchim Province" }
    ],
    districts: {
        1: ["Bhojpur", "Dhankuta", "Ilam", "Jhapa", "Khotang", "Morang", "Okhaldhunga", "Panchthar", "Sankhuwasabha", "Solukhumbu", "Sunsari", "Taplejung", "Terhathum", "Udayapur"],
        2: ["Parsa", "Bara", "Rautahat", "Sarlahi", "Dhanusha", "Mahottari", "Saptari", "Siraha"],
        3: ["Sindhuli", "Ramechhap", "Dolakha", "Bhaktapur", "Dhading", "Kathmandu", "Kavrepalanchok", "Lalitpur", "Nuwakot", "Rasuwa", "Sindhupalchok", "Chitwan", "Makwanpur"],
        4: ["Baglung", "Gorkha", "Kaski", "Lamjung", "Manang", "Mustang", "Myagdi", "Nawalpur", "Parbat", "Syangja", "Tanahun"],
        5: ["Kapilvastu", "Parasi", "Rupandehi", "Arghakhanchi", "Gulmi", "Palpa", "Dang", "Pyuthan", "Rolpa", "Eastern Rukum", "Banke", "Bardiya"],
        6: ["Western Rukum", "Salyan", "Dolpa", "Humla", "Jumla", "Kalikot", "Mugu", "Surkhet", "Dailekh", "Jajarkot"],
        7: ["Kailali", "Achham", "Doti", "Bajhang", "Bajura", "Kanchanpur", "Dadeldhura", "Baitadi", "Darchula"]
    }
};

// Global helper to filter districts by province name
function getDistrictsByProvinceName(provinceName) {
    if (!provinceName) return [];
    
    // Normalize: remove " Province" if present for matching
    const searchName = provinceName.toLowerCase().replace(" province", "").trim();
    
    const province = nepalData.provinces.find(p => {
        const pName = p.name.toLowerCase().replace(" province", "").trim();
        return pName === searchName;
    });
    
    return province ? nepalData.districts[province.id] : [];
}

if (typeof window !== 'undefined') {
    window.nepalData = nepalData;
    window.getDistrictsByProvinceName = getDistrictsByProvinceName;
    
    // UI Helper to populate a province select
    window._populateProvinceSelect = (selectId) => {
        const sel = document.getElementById(selectId);
        if(!sel) return;
        sel.innerHTML = '<option value="">Select Province</option>';
        nepalData.provinces.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.textContent = p.name;
            sel.appendChild(opt);
        });
    };

    // UI Helper to update district select based on province
    window._updateDistrictSelect = (provinceName, districtSelectId) => {
        const sel = document.getElementById(districtSelectId);
        if(!sel) return;
        
        const districts = getDistrictsByProvinceName(provinceName);
        console.log(`Updating districts for ${provinceName}:`, districts);
        
        sel.innerHTML = '<option value="">Select District</option>';
        districts.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            sel.appendChild(opt);
        });
    };

    console.log("Nepal Data Script Loaded and Helpers Initialized");
}
