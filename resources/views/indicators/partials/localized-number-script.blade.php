<script>
    /**
     * Gemelo de App\Support\LocalizedNumber: punto para miles, coma para decimales.
     * "7.547" -> "7547", "5.959,82" -> "5959.82", "85,6" -> "85.6". Null si no se puede leer.
     */
    window.parseLocalizedNumber = function (value) {
        if (value === null || value === undefined) return null;

        let text = String(value).replace(/[\s $]/g, '');
        if (text === '') return null;

        let sign = '';
        if (text[0] === '-' || text[0] === '+') {
            sign = text[0] === '-' ? '-' : '';
            text = text.slice(1);
        }

        const hasDot = text.includes('.');
        const hasComma = text.includes(',');
        let number;

        if (hasDot && hasComma) {
            const decimal = text.lastIndexOf(',') > text.lastIndexOf('.') ? ',' : '.';
            const thousands = decimal === ',' ? '.' : ',';
            const quote = c => (c === '.' ? '\\.' : c);
            if (!new RegExp(`^\\d{1,3}(${quote(thousands)}\\d{3})*${quote(decimal)}\\d+$`).test(text)) return null;
            number = text.split(thousands).join('').replace(decimal, '.');
        } else if (hasComma) {
            if ((text.match(/,/g) || []).length === 1) {
                if (!/^\d*,\d+$/.test(text)) return null;
                number = text.replace(',', '.');
            } else {
                if (!/^\d{1,3}(,\d{3})+$/.test(text)) return null;
                number = text.split(',').join('');
            }
        } else if (hasDot) {
            if (text[0] !== '0' && /^\d{1,3}(\.\d{3})+$/.test(text)) {
                number = text.split('.').join('');
            } else if (/^\d*\.\d+$/.test(text)) {
                number = text;
            } else {
                return null;
            }
        } else {
            if (!/^\d+$/.test(text)) return null;
            number = text;
        }

        if (number.includes('.')) number = number.replace(/0+$/, '').replace(/\.$/, '');
        number = number.replace(/^0+/, '');
        if (number === '' || number[0] === '.') number = '0' + number;

        return number === '0' ? '0' : sign + number;
    };
</script>
