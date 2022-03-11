# DynamoDB pagination

## LastEvaluatedKey / ExclusiveStartKey

DynamoDB return an array as LastEvaluatedKey after each query.   
We must add the LastEvaluatedKey as ExclusiveStartKey to the next query to fetch the next result.

## Prev/Next
To retrieve the previous result, we must change 2 parameters:
- ScanIndexForward must be reversal 
- ExclusiveStartKey must be contained the first item returned from the previous query

## Cursor values
The cursor values must contain these data:
- item keys (pk, [sk, lsi])
- direction

## Cases

DynamoDB Items:

| PK  | SK  | LSI  |
|:----|:----|:-----|
| foo | 1   | null |
| foo | 2   | null |
| foo | 3   | null |
| foo | 4   | null |
| foo | 5   | null |


### First query

####Params
| Name             | Value |  
|:-----------------|:------|
| PK               | foo   | 
| Limit            | 2     | 
| ScanIndexForward | true  | 

####Cursor
Next
````json
{
  "keys": {
    "PK": "foo",
    "SK": 2
  },
  "direction": "ASC"
}
````
Previous
``null``

### Next #1

####Params
| Name              | Value                    |  
|:------------------|:-------------------------|
| PK                | foo                      | 
| Limit             | 2                        | 
| ScanIndexForward  | true                     | 
| ExclusiveStartKey | {"PK": "foo", "SK": "2"} | 

####Cursor
Next
````json
{
  "keys": {
    "PK": "foo",
    "SK": 4
  },
  "direction": "ASC"
}
````
Previous
````json
{
  "keys": {
    "PK": "foo",
    "SK": 3
  },
  "direction": "DESC"
}
````


### Next #2

####Params
| Name              | Value                    |  
|:------------------|:-------------------------|
| PK                | foo                      | 
| Limit             | 2                        | 
| ScanIndexForward  | true                     | 
| ExclusiveStartKey | {"PK": "foo", "SK": "4"} | 

####Cursor
Next
``null``   

Previous
````json
{
  "keys": {
    "PK": "foo",
    "SK": 5
  },
  "direction": "DESC"
}
````