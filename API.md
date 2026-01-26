# API – CMS Headless

  
Base URL : `http://localhost:8080`

  
---

  
## GET /api/posts

Retourne la liste des articles publiés au format JSON.  
### Requête  
```http  
GET /api/posts  
Accept: application/json  
Réponse 200  
[  
  {  
    "id": 1

    "title": "Article title",

    "slug": "article-slug"

  
  }  
]

  
### Codes HTTP

200 : Succès

404 : Ressource non trouvée

500 : Erreur serveur