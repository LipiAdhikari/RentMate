@props(['hasNepaliCalendar' => false])

<script>
    function syncFromNepaliMonth() {
        const bsMonth = parseInt(document.getElementById('bs_month')?.value) || 8;
        const bsYear = parseInt(document.getElementById('bs_year')?.value) || 2083;

        // Approximate map from BS to AD month
        // Baisakh (1) -> April (4), Jestha (2) -> May (5), ..., Mangsir (8) -> November (11)
        let adMonth = bsMonth + 3;
        let adYear = bsYear - 57;
        if (adMonth > 12) {
            adMonth -= 12;
            adYear += 1;
        }

        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');
        if (monthSelect) monthSelect.value = adMonth;
        if (yearSelect) yearSelect.value = adYear;
    }

    let expenseRowIndex = 0;

    function addExpenseRow(title = '', amount = '', category = 'Waste') {
        const container = document.getElementById('expenses-container');
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-3 items-center expense-row bg-slate-50 p-2.5 rounded-xl border border-slate-200';
        row.id = `expense-row-${expenseRowIndex}`;
        row.innerHTML = `
            <div class="col-span-5">
                <input type="text" name="expenses[${expenseRowIndex}][title]" value="${title}" placeholder="Charge Title" required class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-1.5 border">
            </div>
            <div class="col-span-3">
                <input type="number" step="0.01" min="0" name="expenses[${expenseRowIndex}][amount]" value="${amount}" placeholder="Amount (NPR)" required oninput="calculateAll()" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-1.5 border expense-amount-input">
            </div>
            <div class="col-span-3">
                <select name="expenses[${expenseRowIndex}][category]" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-2 py-1.5 border">
                    <option value="Waste" ${category === 'Waste' ? 'selected' : ''}>Waste</option>
                    <option value="Maintenance" ${category === 'Maintenance' ? 'selected' : ''}>Maintenance</option>
                    <option value="Internet" ${category === 'Internet' ? 'selected' : ''}>Internet</option>
                    <option value="Other" ${category === 'Other' ? 'selected' : ''}>Other</option>
                </select>
            </div>
            <div class="col-span-1 text-right">
                <button type="button" onclick="removeExpenseRow(${expenseRowIndex})" class="text-rose-500 hover:text-rose-700 p-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        expenseRowIndex++;
        if (window.lucide) window.lucide.createIcons();
        calculateAll();
    }

    function removeExpenseRow(idx) {
        const row = document.getElementById(`expense-row-${idx}`);
        if (row) {
            row.remove();
            calculateAll();
        }
    }

    function calculateAll() {
        const rent = parseFloat(document.getElementById('rent_amount')?.value) || 0;
        const prevReading = parseFloat(document.getElementById('prev_electricity_reading')?.value) || 0;
        const currReading = parseFloat(document.getElementById('curr_electricity_reading')?.value) || 0;
        const elecRate = parseFloat(document.getElementById('electricity_rate')?.value) || 0;
        const totalWater = parseFloat(document.getElementById('total_water_bill')?.value) || 0;
        const waterPct = parseFloat(document.getElementById('tenant_water_percentage')?.value) || 0;

        let units = 0;
        const warning = document.getElementById('reading-warning');

        if (currReading < prevReading) {
            if (warning) warning.classList.remove('hidden');
            units = 0;
        } else {
            if (warning) warning.classList.add('hidden');
            units = currReading - prevReading;
        }

        const elecBill = units * elecRate;
        document.getElementById('calc-units').innerText = units.toFixed(2);
        document.getElementById('calc-electricity-bill').innerText = elecBill.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const tenantWater = totalWater * (waterPct / 100);
        const ownerWater = totalWater - tenantWater;
        document.getElementById('calc-tenant-water').innerText = tenantWater.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('calc-owner-water').innerText = 'NPR ' + ownerWater.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        let otherTotal = 0;
        document.querySelectorAll('.expense-amount-input').forEach(input => {
            otherTotal += parseFloat(input.value) || 0;
        });

        const grandTotal = rent + elecBill + tenantWater + otherTotal;
        document.getElementById('calc-grand-total').innerText = grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['rent_amount', 'prev_electricity_reading', 'curr_electricity_reading', 'electricity_rate', 'total_water_bill', 'tenant_water_percentage'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', calculateAll);
        });

        calculateAll();
    });
</script>
