function toggleSelect(checkId, selectId, titleId) {
    const isChecked = document.getElementById(checkId).checked;
    const selectElement = document.getElementById(selectId);
    const titleElement = document.getElementById(titleId);

    selectElement.disabled = !isChecked;
    selectElement.style.background = isChecked ? '#ff6a13' : '#fff';
    titleElement.style.color = isChecked ? 'white' : '#ff4d00';
}

// Initial state
for (let i = 1; i <= 12; i++) {
    toggleSelect(`id_check_agree_${i}`, `select-${i}`, `title-${i}`);
}

// Functions to be called on checkbox change
function createCheckAgreeFunc(i) {
    return function() {
        const currentCheckbox = document.getElementById(`id_check_agree_${i}`);
        const currentTime = currentCheckbox.dataset.time;

        toggleSelect(`id_check_agree_${i}`, `select-${i}`, `title-${i}`);

        // Re-evaluate all checkboxes
        reevaluateCheckboxes();
    };
}

for (let i = 1; i <= 12; i++) {
    window[`check_agree_func_${i}`] = createCheckAgreeFunc(i);
}

function isTimeOverlap(time1, time2) {
    const [start1, end1] = time1.split('-').map(t => t.replace(':', ''));
    const [start2, end2] = time2.split('-').map(t => t.replace(':', ''));
    return !(end1 <= start2 || end2 <= start1);
}

function reevaluateCheckboxes() {
    const checkboxes = document.getElementsByName('check_agree');
    for (let checkbox of checkboxes) {
        if (!checkbox.checked) {
            const currentTime = checkbox.dataset.time;
            let overlap = false;
            for (let otherCheckbox of checkboxes) {
                if (otherCheckbox !== checkbox && otherCheckbox.checked && isTimeOverlap(otherCheckbox.dataset.time, currentTime)) {
                    overlap = true;
                    break;
                }
            }
            checkbox.disabled = overlap;
            const selectId = checkbox.id.replace('id_check_agree_', 'select-');
            document.getElementById(selectId).style.background = overlap ? '#d3d3d3' : '#fff';
        }
    }
}

function radioDeselection() {
    document.getElementsByName('check_agree').forEach(element => {
        element.checked = false;
        element.disabled = false;
        const selectId = element.id.replace('id_check_agree_', 'select-');
        document.getElementById(selectId).style.background = '#fff';
    });

    for (let i = 1; i <= 12; i++) {
        toggleSelect(`id_check_agree_${i}`, `select-${i}`, `title-${i}`);
    }
}