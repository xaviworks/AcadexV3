// Listen for installation
chrome.runtime.onInstalled.addListener(function() {
  console.log('ACADEX Password Manager installed');
  
  // Default accounts created by the initial-accounts CLI command
  const defaultAccounts = [
    {
      username: 'admin',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    },
    {
      username: 'chairperson.bsit',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    },
    {
      username: 'instructor.bsit',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    },
    {
      username: 'chairperson.bsba',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    },
    {
      username: 'instructor.bsba',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    },
    {
      username: 'chairperson.bspsy',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    },
    {
      username: 'dean',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    },
    {
      username: 'instructor.bspsy',
      password: 'password',
      createdAt: new Date().toISOString(),
      lastUsed: null
    }
  ];

  // Save default accounts to storage
  chrome.storage.sync.set({ passwords: defaultAccounts }, function() {
    console.log('Default accounts seeded successfully');
  });
});

// Listen for messages from popup
chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
  if (request.action === "getPasswords") {
    chrome.storage.sync.get(['passwords'], function(result) {
      sendResponse(result.passwords || {});
    });
    return true; // Required for async sendResponse
  }
});
