window.DateTime = {
  getDatetimeLocalValue: function (elementId, date = new Date()) {
    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - offset * 60000);
    const value = localDate.toISOString().slice(0, 16);
    document.getElementById(elementId).value = value;
  },
};
