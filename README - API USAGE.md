
## About TFT

TFT is a ugandan online marketplace for lodging, primarily homestays for vacation rentals, and tourism activities:



Getting started with the API USAGE.....

## Registering a USER in the system.

Endpoint: /api/register 
METHOD: POST

PAYLOAD:

{
  "name": "string",
  "currency": "string",
  "email": "string",
  "phone": "number",
  "password": "string",
  "country": "string",
  "device_id": "string",
  "device_os": "string",
}

RESPONSES

 SUCCESS CODE: 200 OK
 SUCCESS BODY:
 {
    "token_type": "Bearer",
    "expires_in": 31536000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYWEwMmVjNGM3YWYyZmI2M2JmOTcwYWE0OTIyZDVhOTE4NmJlMWU4NzAwNjBkNmY3ZjVkNzlmZDM4YWE4YjZjMjJhYzgxOTA0OTE4OWE0ODUiLCJpYXQiOjE2NTI3OTcxMjQuMjc4NzcxLCJuYmYiOjE2NTI3OTcxMjQuMjc4Nzg3LCJleHAiOjE2ODQzMzMxMjQuMDQ1MTM5LCJzdWIiOiIxMiIsInNjb3BlcyI6W119.Nx3Jtzqsny2xqrWlVo62Q6U4yFV42MFNvm3NHh47MKscQ4_2N2bvbGnVTBAe_fPFayLqzbOC8QonipX0hRdtV2CYHRJnMoR_zvfucNaxdqwFdesINifFO1B4mpphv-KHRprczkHRYkWUeAssIN_HEaU_pFzK_fP6DXeLeirAxxH9HU2uAtTeR7b-7nZrzd2c4O7Rlw-ZWnc12i4nbw2v3JfR4uuEZuPJPLvPfxK1qnaPzPtkGRC4fay1MJdZf_0OAtrC0LDhjxGEbWDz9n0EUWkOmtAWv1tt5z3IvFbPoFmxC6dXQCz_BGBVwvlqT_gIR-j8a9v2dAkugwBFYmZb89ok3z-VwssvcdaeTA35PU8DZ9pT0rFkX1XKuG8-2U__vsX7UWiyQ2AwynAPb37ca6OAOsZlP3pj8ukPUYs66AONwG4RkIoBt9GzWCeEDR9pov0Q4sp0ALtFUrnR_ySS0nwOuQ8YczRIbUOqT4VkO2pph8EKS2J5y20obgXPXMRQfYmcdS2gbmI4desbw3SaS34uLw_Ndw27-asDASskY2wl_2xpiQZ4E3OQ9PRP7jA0snWks7t7iOLgXIIvmLaxRU2l9LEusC5sd5RN4NZB7gBAAcCeLpwZHJ_PVaPoNslZrEjWzs6NgoPIxbcilWaG4B-tqwgvVm7_qe5na66pYtE",
    "refresh_token": "def5020099abb40cfe9e35328c03929f68c6b69a08dfe2b4196537074fa14ed0d024e3b68ac100d4e597b753d660f346d961b7e0944f9d344468c7e180ccc2968faafefd87e38c78d3d11fb230855542496f66fead456a2216a5d7d47d7d29eecff34933f6c261256e7b0cb635d438b63caf11d20b046c26919398cafc29eca71bdbe16706f08ab51076e29d48bbb0472caea4f29ede662b16872f4ce4bbf88717e2e9d9947574d0b6a54582a138e5e838e2331f81ee47759cbd6191cc6dcdbb2d6cffc4848363e6ac7b5c619ccf2c87122b2dd07daea69428aa4940e0362b525ca960e22609b94c85ec671805bd4c409e5270dc0c31470c5858148cc600420147ca70a73b5b863d4e24a4ef4cc008b573727eebbbffecf5db1ff1f0f94836503f8553b64953174d31a370ffb6a2d9c5e8ea3ff030eaabf3a26725f5c73b9876f4cc76eb18a73d69e88e4a88f5e5c00f4f3f7a63215e7b8ac815fb653a6b02cda3f2c9e4"
}

ERROR CODE :4**
ERROR BODY :
{
    "status": 400,
    "message": "Email Already Registered!",
    "code": 400
}




## USER LOGIN

Endpoint: /api/login 
METHOD: POST

PAYLOAD:

{
  "username": "string",
  "password": "string",
  "device_id": "string",
  "device_os": "string",
}

RESPONSES

 SUCCESS CODE: 200 OK
 SUCCESS BODY:
 {
    "token_type": "Bearer",
    "expires_in": 31536000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYWEwMmVjNGM3YWYyZmI2M2JmOTcwYWE0OTIyZDVhOTE4NmJlMWU4NzAwNjBkNmY3ZjVkNzlmZDM4YWE4YjZjMjJhYzgxOTA0OTE4OWE0ODUiLCJpYXQiOjE2NTI3OTcxMjQuMjc4NzcxLCJuYmYiOjE2NTI3OTcxMjQuMjc4Nzg3LCJleHAiOjE2ODQzMzMxMjQuMDQ1MTM5LCJzdWIiOiIxMiIsInNjb3BlcyI6W119.Nx3Jtzqsny2xqrWlVo62Q6U4yFV42MFNvm3NHh47MKscQ4_2N2bvbGnVTBAe_fPFayLqzbOC8QonipX0hRdtV2CYHRJnMoR_zvfucNaxdqwFdesINifFO1B4mpphv-KHRprczkHRYkWUeAssIN_HEaU_pFzK_fP6DXeLeirAxxH9HU2uAtTeR7b-7nZrzd2c4O7Rlw-ZWnc12i4nbw2v3JfR4uuEZuPJPLvPfxK1qnaPzPtkGRC4fay1MJdZf_0OAtrC0LDhjxGEbWDz9n0EUWkOmtAWv1tt5z3IvFbPoFmxC6dXQCz_BGBVwvlqT_gIR-j8a9v2dAkugwBFYmZb89ok3z-VwssvcdaeTA35PU8DZ9pT0rFkX1XKuG8-2U__vsX7UWiyQ2AwynAPb37ca6OAOsZlP3pj8ukPUYs66AONwG4RkIoBt9GzWCeEDR9pov0Q4sp0ALtFUrnR_ySS0nwOuQ8YczRIbUOqT4VkO2pph8EKS2J5y20obgXPXMRQfYmcdS2gbmI4desbw3SaS34uLw_Ndw27-asDASskY2wl_2xpiQZ4E3OQ9PRP7jA0snWks7t7iOLgXIIvmLaxRU2l9LEusC5sd5RN4NZB7gBAAcCeLpwZHJ_PVaPoNslZrEjWzs6NgoPIxbcilWaG4B-tqwgvVm7_qe5na66pYtE",
    "refresh_token": "def5020099abb40cfe9e35328c03929f68c6b69a08dfe2b4196537074fa14ed0d024e3b68ac100d4e597b753d660f346d961b7e0944f9d344468c7e180ccc2968faafefd87e38c78d3d11fb230855542496f66fead456a2216a5d7d47d7d29eecff34933f6c261256e7b0cb635d438b63caf11d20b046c26919398cafc29eca71bdbe16706f08ab51076e29d48bbb0472caea4f29ede662b16872f4ce4bbf88717e2e9d9947574d0b6a54582a138e5e838e2331f81ee47759cbd6191cc6dcdbb2d6cffc4848363e6ac7b5c619ccf2c87122b2dd07daea69428aa4940e0362b525ca960e22609b94c85ec671805bd4c409e5270dc0c31470c5858148cc600420147ca70a73b5b863d4e24a4ef4cc008b573727eebbbffecf5db1ff1f0f94836503f8553b64953174d31a370ffb6a2d9c5e8ea3ff030eaabf3a26725f5c73b9876f4cc76eb18a73d69e88e4a88f5e5c00f4f3f7a63215e7b8ac815fb653a6b02cda3f2c9e4"
}

ERROR CODE :4**
ERROR BODY :
{
    "status": 400,
    "message": "string",
    "code": 400
}

### GET USER INFORMATION

Endpoint: /api/user 

METHOD: GET

PAYLOAD:

{
  
}

HEADERS:
{
    'Authorization'=> 'Bearer '.$access_token,
    'Accept'=>'application/json'
}


RESPONSES

 SUCCESS CODE: 200 OK

 SUCCESS BODY:
 {
    "id": "fe3eef04-60e5-4f97-956a-c82c6c05bc92",
    "role": "user",
    "name": "TFT Wallet User",
    "email": "tftwalletuser@tft.com",
    "phone": "700000000",
    "country": "256",
    "premuim": 0,
    "manual_premuim": 0,
    "email_verified_at": "2022-05-17 13:52:58",
    "status": 1,
    "type": null,
    "usertype": null,
    "login_status": 0,
    "google_id": null,
    "user_address": null,
    "plan_amount": null,
    "session_id": null,
    "confirmation_code": null,
    "pack_name": null,
    "pack_id": null,
    "transaction_id": null,
    "start_at": null,
    "expired_in": null,
    "avatar": "http://127.0.0.3:8000/api/avatars/image/avatar_default.png",
    "created_at": "2022-05-17T13:52:59.000000Z",
    "updated_at": "2022-05-17T13:52:59.000000Z",
    "stripe_id": null,
    "card_brand": null,
    "card_last_four": null,
    "trial_ends_at": null,
    "favoriteTours": [],
    "favoritePlaces": [],
    "favoriteHotels": [],
    "favoriteMeals": []
}

ERROR CODE :4**

ERROR BODY :
{
    "status": 400,
    "message": "message",
    "code": 400
}


## GET USER WALLET


Endpoint: /api/user/wallet

METHOD: GET

PAYLOAD:

{
  
}

HEADERS:
{
    'Authorization'=> 'Bearer '.$access_token,
    'Accept'=>'application/json'
}


RESPONSES

 SUCCESS CODE: 200 OK

 SUCCESS BODY:

{
    "balance": "100",
    "currency": "UGX"
}

ERROR CODE :4**

ERROR BODY :
{
    "status": 400,
    "message": "message",
    "code": 400
}

## API STILL UNDER DEVELOPEMENT CHECK BACK LATER.
