function getDateFrom() {
    return formatDateForFetch(selectedPeriod[0]);
}

function getDateTo() {
    return formatDateForFetch(selectedPeriod[1]);
}

function getDefaultDateRanges() {
    const now = new Date();
    const today = [new Date(), new Date()];
    const yesterday = [new Date(now.setDate(now.getDate() - 1)), new Date(now)];

    const startOfThisWeek = new Date(now.setDate(now.getDate() - now.getDay() + 1));
    const thisWeek = [new Date(startOfThisWeek), new Date()];

    const startOfLastWeek = new Date(now.setDate(now.getDate() - now.getDay() - 6));
    const endOfLastWeek = new Date(now.setDate(startOfLastWeek.getDate() + 6));
    const lastWeek = [startOfLastWeek, endOfLastWeek];

    const startOfThisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const thisMonth = [startOfThisMonth, new Date()];

    const startOfLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const endOfLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
    const lastMonth = [startOfLastMonth, endOfLastMonth];

    const startOfThisYear = new Date(now.getFullYear(), 0, 1);
    const thisYear = [startOfThisYear, new Date()];

    const startOfLastYear = new Date(now.getFullYear() - 1, 0, 1);
    const endOfLastYear = new Date(now.getFullYear() - 1, 11, 31);
    const lastYear = [startOfLastYear, endOfLastYear];

    const allTime = [new Date(0), new Date()];

    return {
        today: today,
        yesterday: yesterday,
        this_week: thisWeek,
        last_week: lastWeek,
        this_month: thisMonth,
        last_month: lastMonth,
        this_year: thisYear,
        last_year: lastYear,
        all_time: allTime,
    };
}

function resetTime(date) {
    const d = typeof date === 'string' ? new Date(date) : date;

    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };

    return new Date(date).toLocaleDateString('en-US', options);
}

function formatDateForFetch(date) {
    if (!date) {
        return '';
    }

    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}