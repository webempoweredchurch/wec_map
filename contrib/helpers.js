Array.flatten = function(array, excludeUndefined) {
  if (excludeUndefined === undefined) {
    excludeUndefined = false;
  }
  var result = [];
  var len = array.length;
  for (var i = 0; i < len; i++) {
    var el = array[i];
    if (el instanceof Array) {
      var flat = el.flatten(excludeUndefined);
      result = result.concat(flat);
    } else if (!excludeUndefined || el != undefined) {
      result.push(el);
    }
  }
  return result;
};

if (!Array.prototype.flatten) {
  Array.prototype.flatten = function(excludeUndefined) {
    return Array.flatten(this, excludeUndefined);
  }
}