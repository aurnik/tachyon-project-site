// Check if Storage is available and enabled
var storageAvailable = false;
var test = true;
try {
    localStorage.setItem(test, test);
    localStorage.removeItem(test);
    storageAvailable = true;
} catch (e) {
    storageAvailable = false;
}
