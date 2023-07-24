export const HttpClient = {
  async get(url, params = {}) {
    // Déterminer le caractère à utiliser pour ajouter des paramètres à l'URL
    const paramCharacter = url.includes('?') ? '&' : '?';
  
    // Ajouter des paramètres à l'URL si nécessaire
    let urlWithParams = url;
    if (Object.keys(params).length > 0) {
      urlWithParams += `${paramCharacter}${new URLSearchParams(params)}`;
    }
  
    const response = await fetch(urlWithParams);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
  
    const data = await response.json();
    return data;
  },
  
  

  async post(url, params = {}) {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(params),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    return data;
  },
};
