/**
 * Get config value
 *
 * @param {String} name
 * @param {Object} configs
 * @returns {String}
 */
exports.getConfigValue = function(name, configs) {
    return configs[name] || '${' + name + '}';
}
