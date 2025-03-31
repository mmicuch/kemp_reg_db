function validateAge() {
    const dobInput = document.getElementById('datum_narodenia'); // Input element pre dátum narodenia
    const dob = dobInput.value; // Hodnota dátumu narodenia

    if (dob) {
        const birthDate = new Date(dob); // Konverzia dátumu narodenia na objekt Date
        const currentYear = new Date().getFullYear(); // Aktuálny rok
        const birthYear = birthDate.getFullYear(); // Rok narodenia
        const ageThisYear = currentYear - birthYear; // Vek účastníka v aktuálnom roku

        // Ak tento rok dosiahne vek 14 alebo je stále vo veku 26, registrácia je povolená
        if (ageThisYear < 14 || ageThisYear > 26) {
            alert("Účastníci musia mať tento rok vek medzi 14 a 26 rokmi.");
            dobInput.value = ""; // Reset hodnoty, ak vek nevyhovuje
        }
    }
}

// Aktualizácia ubytovania po načítaní stránky
window.addEventListener('DOMContentLoaded', () => {
    updateAccommodation();
});

// Listener pre zmenu pohlavia
document.getElementById('pohlavie').addEventListener('change', updateAccommodation);

function updateAccommodation() {
    const genderSelect = document.getElementById('pohlavie');
    const gender = genderSelect.value;
    const accommodationSelect = document.getElementById('ubytovanie');
    
    // Ak nie je vybrané pohlavie, nevykonávaj filter
    if (!gender) return;
    
    // Nastavenie defaultnej hodnoty
    accommodationSelect.selectedIndex = 0;
    
    // Prejdi všetky možnosti a zobraz/skry podľa pohlavia
    for (let i = 0; i < accommodationSelect.options.length; i++) {
        const option = accommodationSelect.options[i];
        if (option.value === "") continue; // Preskočiť placeholder
        
        const type = option.getAttribute('data-type');
        let visible = false;
        
        if (leaderMode === "true") {
            visible = (gender === 'M' && (type === 'muz' || type === 'spolocne' || type === 'veduci')) ||
                      (gender === 'F' && (type === 'zena' || type === 'spolocne' || type === 'veduci'));
        } else {
            visible = (gender === 'M' && (type === 'muz' || type === 'spolocne')) ||
                      (gender === 'F' && (type === 'zena' || type === 'spolocne'));
        }
        
        // Use disabled instead of style.display for options
        option.disabled = !visible;
        option.hidden = !visible;
    }
    
    // Vyber prvú dostupnú možnosť
    for (let i = 0; i < accommodationSelect.options.length; i++) {
        const option = accommodationSelect.options[i];
        if (!option.disabled && option.value !== "") {
            accommodationSelect.value = option.value;
            break;
        }
    }
}

function toggleOtherAllergy() {
    const allergySelect = document.getElementById('alergie');
    const otherInput = document.getElementById('alergie_other');
    // Zisti, či je možnosť "other" vybraná
    const isOtherSelected = Array.from(allergySelect.options)
                                 .some(opt => opt.selected && opt.value === 'other');
    otherInput.style.display = isOtherSelected ? 'block' : 'none';
}


// Pridaj toto do script.js
document.addEventListener('DOMContentLoaded', function() {
    setupMultiStepForm();
});

function setupMultiStepForm() {
    const form = document.querySelector('form');
    
    // Rozdelenie formulára na sekcie
    const sections = [
        { title: "Osobné údaje", fields: ["meno", "priezvisko", "datum_narodenia", "pohlavie", "mladez", "mail"] },
        { title: "Ubytovanie", fields: ["ubytovanie"] },
        { title: "Aktivity", fields: ["aktivity_streda", "aktivity_stvrtok", "aktivity_piatok"] },
        { title: "Alergie a doplnkové informácie", fields: ["alergie", "poznamka", "novy", "gdpr"] }
    ];
    
    // Vytvorenie kontajnera pre kroky a navigáciu
    const stepsContainer = document.createElement('div');
    stepsContainer.className = 'steps-container';
    
    const stepsNav = document.createElement('div');
    stepsNav.className = 'steps-nav';
    
    // Vytvorenie indikátorov krokov
    sections.forEach((section, index) => {
        const stepIndicator = document.createElement('div');
        stepIndicator.className = 'step-indicator';
        stepIndicator.textContent = index + 1;
        stepIndicator.dataset.step = index;
        if (index === 0) stepIndicator.classList.add('active');
        stepsNav.appendChild(stepIndicator);
    });
    
    stepsContainer.appendChild(stepsNav);
    form.prepend(stepsContainer);
    
    // Vytvorenie skupín polí
    const fieldGroups = [];
    sections.forEach((section, index) => {
        const fieldGroup = document.createElement('div');
        fieldGroup.className = 'form-section';
        fieldGroup.dataset.step = index;
        
        const heading = document.createElement('h2');
        heading.textContent = section.title;
        fieldGroup.appendChild(heading);
        
        // Presun relevantných polí do tejto skupiny
        section.fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const fieldContainer = field.closest('div') || field;
                const label = document.querySelector(`label[for="${fieldId}"]`);
                
                if (label) fieldGroup.appendChild(label);
                fieldGroup.appendChild(fieldContainer || field);
            }
        });
        
        // Skryť všetky skupiny okrem prvej
        if (index > 0) fieldGroup.style.display = 'none';
        
        form.appendChild(fieldGroup);
        fieldGroups.push(fieldGroup);
    });
    
    // Pridanie navigačných tlačidiel
    const navButtons = document.createElement('div');
    navButtons.className = 'form-navigation';
    
    const prevButton = document.createElement('button');
    prevButton.type = 'button';
    prevButton.className = 'prev-button';
    prevButton.textContent = 'Predchádzajúci';
    prevButton.style.display = 'none';
    
    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.className = 'next-button';
    nextButton.textContent = 'Ďalší';
    
    const submitButton = document.querySelector('button[type="submit"]');
    submitButton.style.display = 'none';
    
    navButtons.appendChild(prevButton);
    navButtons.appendChild(nextButton);
    form.appendChild(navButtons);
    
    // Aktuálny krok
    let currentStep = 0;
    
    // Event listenery pre navigáciu
    nextButton.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            fieldGroups[currentStep].style.display = 'none';
            currentStep++;
            fieldGroups[currentStep].style.display = 'block';
            
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentStep);
            });
            
            prevButton.style.display = 'inline-block';
            
            if (currentStep === sections.length - 1) {
                nextButton.style.display = 'none';
                submitButton.style.display = 'block';
            }
        }
    });
    
    prevButton.addEventListener('click', function() {
        fieldGroups[currentStep].style.display = 'none';
        currentStep--;
        fieldGroups[currentStep].style.display = 'block';
        
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentStep);
        });
        
        if (currentStep === 0) {
            prevButton.style.display = 'none';
        }
        
        nextButton.style.display = 'inline-block';
        submitButton.style.display = 'none';
    });
}

function validateStep(step) {
    // Implementácia validácie pre každý krok
    // Toto je len základná verzia, môžeš ju rozšíriť podľa potreby
    const currentStepFields = document.querySelector(`.form-section[data-step="${step}"]`)
                              .querySelectorAll('input[required], select[required]');
    
    let isValid = true;
    
    currentStepFields.forEach(field => {
        if (!field.value) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    if (!isValid) {
        alert('Prosím, vyplňte všetky povinné polia');
    }
    
    return isValid;
}