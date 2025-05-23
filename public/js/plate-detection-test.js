// Utility script untuk testing sanitasi plat nomor
(function() {
    console.log("Test script loaded, checking sanitization features...");
    
    // Test cases untuk sanitasi plat
    const testCases = [
        "B123ABC", // Basic case
        "B 123 ABC", // With spaces
        "b123abc", // Lowercase
        "B-123-ABC", // With dashes
        "B.123.ABC", // With dots
        "B123", // Incomplete
        "BBBB1234ABCDEF", // Too long
        "1234ABC", // Missing region
        "B1234", // Missing letters
        "BABC", // Missing numbers
        "O1234BCD", // O vs 0
        "B1234I", // I vs 1
        "BSZ34ABC", // S vs 5, Z vs 2
        "BB1GTZABC" // More mixups
    ];
    
    // Run tests if sanitizeLicensePlate is available
    function runTests() {
        if (typeof window.sanitizeLicensePlate !== 'function') {
            console.error("sanitizeLicensePlate function not available!");
            return false;
        }
        
        console.log("============ PLATE SANITIZATION TESTS ============");
        
        let passed = 0;
        let failed = 0;
        
        testCases.forEach(test => {
            try {
                const result = window.sanitizeLicensePlate(test);
                
                // Basic validation
                const isValid = /^[A-Z]{1,2}\s[0-9]{1,4}\s[A-Z]{1,3}$/.test(result);
                
                if (isValid) {
                    console.log(`✓ PASS: "${test}" -> "${result}"`);
                    passed++;
                } else {
                    console.error(`✗ FAIL: "${test}" -> "${result}" (invalid format)`);
                    failed++;
                }
            } catch (e) {
                console.error(`✗ ERROR: "${test}" -> ${e.message}`);
                failed++;
            }
        });
        
        console.log("============ TEST RESULTS ============");
        console.log(`Total: ${testCases.length}, Passed: ${passed}, Failed: ${failed}`);
        console.log("====================================");
        
        return failed === 0;
    }
    
    // Expose test function
    window.testPlateSanitization = function() {
        return runTests();
    };
    
    // Run tests automatically after 2 seconds
    setTimeout(() => {
        if (document.readyState === 'complete') {
            runTests();
        }
    }, 2000);
})();