# API Documentation - SPK Bahan Ajar Praktikum

## Overview
Sistem SPK Bahan Ajar Praktikum menggunakan metode AHP (Analytical Hierarchy Process) untuk perangkingan pengajuan bahan ajar.

## Base URL
```
http://localhost:8000
```

## Authentication
Sistem menggunakan Laravel Sanctum untuk authentication. Semua endpoint memerlukan authentication kecuali yang disebutkan sebagai public.

## Response Format
Semua API response menggunakan format yang konsisten:

### Success Response
```json
{
    "success": true,
    "message": "Success message",
    "data": {...},
    "timestamp": "2024-01-01T00:00:00.000000Z",
    "status_code": 200
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "Error message"
    },
    "timestamp": "2024-01-01T00:00:00.000000Z",
    "status_code": 400
}
```

## Error Codes
| Code | Description | HTTP Status |
|------|-------------|-------------|
| `VALIDATION_ERROR` | Data validation failed | 422 |
| `NOT_FOUND` | Resource not found | 404 |
| `UNAUTHORIZED` | Authentication required | 401 |
| `FORBIDDEN` | Access denied | 403 |
| `DATABASE_ERROR` | Database operation failed | 500 |
| `AHP_CALCULATION_ERROR` | AHP calculation failed | 500 |
| `MATRIX_INCOMPLETE` | AHP matrix incomplete | 400 |
| `INCONSISTENT_MATRIX` | AHP matrix inconsistent | 400 |
| `EXPORT_ERROR` | Export operation failed | 500 |

## Endpoints

### 1. Health Check
```
GET /health
```
**Description:** Check system health status
**Response:** System health information including database, queue, storage, and AHP system status

### 2. AHP API Endpoints

#### Calculate AHP Weights
```
POST /api/ahp/calculate-weights
```
**Description:** Calculate AHP weights and consistency for a session
**Body:**
```json
{
    "session_id": 1
}
```
**Response:** AHP calculation results with weights and consistency metrics

#### Generate AHP Results
```
POST /api/ahp/generate-results
```
**Description:** Generate complete AHP results including rankings
**Body:**
```json
{
    "session_id": 1
}
```
**Response:** Complete AHP results with rankings

#### Get AHP Results
```
GET /api/ahp/results/{session_id}
```
**Description:** Retrieve AHP results for a specific session
**Response:** AHP results data

#### Get Rankings
```
GET /api/ahp/rankings/{session_id}
```
**Description:** Get rankings for a specific AHP session
**Response:** Ranking data with scores

#### Validate Matrix
```
GET /api/ahp/validate-matrix/{session_id}
```
**Description:** Validate AHP comparison matrix completeness
**Response:** Matrix validation status

#### Save Matrix
```
POST /api/ahp/save-matrix
```
**Description:** Save AHP comparison matrix data
**Body:**
```json
{
    "session_id": 1,
    "matrix_data": {
        "matrix_1_2": 3.0,
        "matrix_1_3": 5.0,
        "matrix_1_4": 2.0,
        "matrix_2_3": 2.0,
        "matrix_2_4": 4.0,
        "matrix_3_4": 3.0
    }
}
```
**Response:** Matrix save confirmation

#### Get Matrix
```
GET /api/ahp/matrix/{session_id}
```
**Description:** Retrieve AHP comparison matrix for a session
**Response:** Matrix data

#### Get Session Statistics
```
GET /api/ahp/statistics/{session_id}
```
**Description:** Get comprehensive statistics for an AHP session
**Response:** Session statistics

### 3. Export Endpoints

#### Export Ranking Per Prodi
```
GET /export/ranking/{prodiId?}
```
**Description:** Export ranking data per program studi
**Parameters:** `prodiId` (optional) - specific program studi ID
**Response:** Excel file download

#### Export Summary Per Prodi
```
GET /export/summary/{sessionId}
```
**Description:** Export summary data per program studi for a session
**Parameters:** `sessionId` - AHP session ID
**Response:** Excel file download

#### Export Procurement List
```
GET /export/procurement/{prodiId?}
```
**Description:** Export procurement list (shopping list format)
**Parameters:** `prodiId` (optional) - specific program studi ID
**Response:** Excel file download

#### Export AHP Results
```
GET /export/ahp-results/{sessionId}
```
**Description:** Export AHP matrix and results
**Parameters:** `sessionId` - AHP session ID
**Response:** Excel file download

#### Show Export Form
```
GET /export/form/{sessionId?}
```
**Description:** Display export form with options
**Parameters:** `sessionId` (optional) - AHP session ID
**Response:** HTML form

#### Get Prodi List
```
GET /export/prodi-list
```
**Description:** Get list of available program studi for export
**Response:** JSON list of program studi

#### Get Export Statistics
```
GET /export/stats
```
**Description:** Get export statistics and data distribution
**Response:** Export statistics data

### 4. Test Endpoints (Development Only)

#### Test AHP Calculation
```
GET /test-ahp/{session_id}
```
**Description:** Test AHP calculation for debugging
**Response:** AHP calculation test results

#### Test Matrix Building
```
GET /test-matrix/{session_id}
```
**Description:** Test AHP matrix building process
**Response:** Matrix building test results

#### Test Complete AHP
```
GET /test-ahp-complete/{session_id}
```
**Description:** Complete AHP testing workflow
**Response:** Complete test results

## AHP Scale Reference
| Value | Meaning |
|-------|---------|
| 1 | Sama penting |
| 2 | Sedikit menuju lebih penting |
| 3 | Sedikit lebih penting |
| 4 | Menuju lebih penting |
| 5 | Lebih penting |
| 6 | Sangat menuju lebih penting |
| 7 | Sangat lebih penting |
| 8 | Sangat menuju mutlak penting |
| 9 | Mutlak lebih penting |

## Consistency Requirements
- **Consistency Ratio (CR) < 0.1** untuk matriks yang konsisten
- **Random Index (RI)** menggunakan tabel Saaty
- **Consistency Index (CI)** dihitung dari eigenvalue maksimum

## Rate Limiting
- Default: 60 requests per minute
- Configurable via environment variables

## Notes
- Semua endpoint memerlukan authentication
- Response format konsisten untuk semua endpoint
- Error handling terstandarisasi
- Logging untuk debugging dan monitoring
- Cache untuk optimasi performa
