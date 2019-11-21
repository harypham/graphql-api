<p align="center"><img src="https://miro.medium.com/max/577/1*VjnyfVHfe0zPJAe_8vrOkA.jpeg" width="400"></p>

## About GraphQL

GraphQL is a query language for APIs and a runtime for fulfilling those queries with your existing data.

GraphQL provides a complete and understandable description of the data in your API, gives clients the power to ask for exactly what they need and nothing more, makes it easier to evolve APIs over time, and enables powerful developer tools.

This is an introduction for building a GraphQL server with [Lighthouse](https://lighthouse-php.com/).



## Setup

1. Clone the repo
2. Add .env file, generate key: 

    ``` cp .env.example .env```

    ```php artisan key:generate```

    Config (.env): Database, mail...

2. Install dependencies && node modules:

    ```composer install```
    
    ```npm install```
    
    ```npm run dev```
4. Create tables db && seeding data:

    ```php artisan migrate --seed```
    
    ```php artisan passport:install```

5. Config (.env): add new lines

        PERSONAL_CLIENT_ID=1
        PERSONAL_CLIENT_SECRET=<key here>
        PASSWORD_CLIENT_ID=2
        PASSWORD_CLIENT_SECRET=<key here>

6. Run app:
        
        php artisan serve

### Structure:
#### **1. Create models**
- Models in Laravel: (./vendor/app/models)
```
php artisan make:model models/ModelName
```
- Defining [relationships](https://laravel.com/docs/5.8/eloquent-relationships) between models
- Defining database migrations:
```
php artisan make:migration create_users_table
```
Run database migrations to create the users table:
```
php artisan migrate
```

#### **2. Create schema**

GraphQL's schema: (./vendor/grapql/schema.graphql)
- Defining Type:
	
    -	**Type** (match with models):
    
         Ex: User type:

         Object User (int id, String name, String email) . 

         A user has many Posts (relationship between User and Post model)
         
          ```
          type User {
              id: ID!
              name: String!
              email: String!
              created_at: DateTime!
              updated_at: DateTime!
              posts: [Post]! @hasMany }
          ```
       	  DateTime is a [Scalar types](https://lighthouse-php.com/4.6/the-basics/types.html#scalar).
          
          Reference a class implementing a scalar definition
GraphQL specification describes several built-in scalar types. In graphql-php they are exposed as static methods of GraphQL\Type\Definition\Type class:
          
          ```
          Type::string();  // String type
          Type::int();     // Int type
          Type::float();   // Float type
          Type::boolean(); // Boolean type
          Type::id();      // ID type
          ```
          
	- Defining **Query**:
	
   		 Every GraphQL schema must have a [`Query`](https://graphql.org/learn/schema/#the-query-and-mutation-types) type which contains the queries your API offers. Think of queries as REST resources which can take arguments and return a fixed result (GET method).
    	
        Ex: 
        ```
        type Query {
              posts: [Post!]! @all
              post(id: Int! @eq): Post @find }	
       ```
		The way that Lighthouse knows how to resolve the `queries` is a combination of convention-based naming - the type name Post is also the name of our Model - and the use of server-side directives.
        
        - [@all](https://lighthouse-php.com/master/api-reference/directives.html#all) returns a list of all Post models
		- [@find](https://lighthouse-php.com/master/api-reference/directives.html#find) and [@eq](https://lighthouse-php.com/master/api-reference/directives.html#eq) are combined to retrieve a single Post by its ID
		- `id: Int!` means that the field is non-nullable (exclamation mark)
		- `[Post!]! ` represents an array of `Post` objects. Since it is also non-nullable , you can always expect an array (with zero or more items)
	- Defining **Mutation**:
     
       In contrast to the `Query` type, the fields of the `Mutation` type are allowed to change data on the server (as POST, PUT, DELETE methods in REST)
       ```
       type Mutation {
            createUser(name: String!, email: String!, password: String!): User
            updateUser(id: ID, email: String, password: String): User
            deleteUser(id: ID): User
          }
       ```
	- Defining **Subscription**:
	
		Rather than providing a single response, the fields of the Subscription type return a stream of responses, with real-time updates.
        ``` 
        type Subscription {
             userUpdated(id: ID): User
                @subscription(class: "App\\GraphQL\\Subscriptions\\UserUpdated")
              }
        ```
        Broadcast event to trigger subscription:
        ```
        type Mutation {
           updateUser(
                  id: ID!,
                  name: String,
              ): User @update   @broadcast(subscription: "userUpdated")
          }
       ```
        Trigger subscription (client implementations): using [Vue Apollo](https://vue-apollo.netlify.com/guide/apollo/subscriptions.html#simple-subscription)
        
        Ex: In vue component, trigger mutaion update User:
        
        ```
        <script>
            import gql from 'graphql-tag';
            export default {
                apollo: {
                    $subscribe: {
                        subscribed: {
                            query: gql`
                                      subscription userUpdated($id: ID) {
                                        userUpdated(id: $id){
                                        id name email
                                        }
                                      }`,
                            variables () {
                                return {
                                    id: this.id,
                                }
                            },
                            result({data}) {
                                console.log(data);
                            },
                        },
                    },
                },
            };
        </script>
        ```

#### **3. Authentication**

- Using [Laravel Passport](https://laravel.com/docs/6.x/passport)
- Handle authentication in graphql (`auth.graphql`):

    Ex: handle login function:
	- Defining Type, Mutation for authentication:
	    - Input object: (the fields of an Input Type are treated similar to arguments)
	      ```
	      input LoginInput {
              username: String!
              password: String!
          }
          ```
        
		- Type:
            ```
            type AuthPayload {
                     access_token: String
                     refresh_token: String
                     expires_in: Int
                     token_type: String
                     user: User!
                          }
            ```
		- Mutation:
			```
			type Mutation {
			        login(input: LoginInput @spread): AuthPayload!
                @field(resolver: "App\\GraphQL\\Mutations\\AuthMutator@login")
			}
            ```
         - [@spread](https://lighthouse-php.com/4.6/api-reference/directives.html#spread):
          merge the fields of a nested input object into the arguments of its parent (use @spread on field arguments or on input object fields)

         - [@field](https://lighthouse-php.com/4.6/api-reference/directives.html#field) :
              assign a resolver function to a field.
              Pass a class and a method to the resolver argument and separate them with an @ symbol. If you pass only a class name, the method name defaults to __invoke.
         - `App\GraphQL\Mutations\AuthMutator@login` is resolver, call `login function` in AuthMutator namespace.
	
	- Apply `auth middleware`:
	
   		 Lighthouse allows you to configure `global middleware` that is run for every request to your endpoint, but also define it on a per-field basis.

		Use the `@middleware` directive to apply Laravel middleware, such as the auth middleware, to selected fields of your GraphQL endpoint.
        ```
        type Query {
              users: [User!]! @middleware(checks: ["auth:api", "custom"]) @all
            }
        ```
        If you need to `apply middleware to multiple fields`, just use @middleware on a type or an extend type definition.
        ```
        extend type Mutation @middleware(checks: ["auth:api"]){
               createUser(name: String!, email: String!, password: String!): User
               updateUser(id: ID, email: String, password: String): User
               deleteUser(id: ID): User
              }
        ```

#### **4. Schema Organization**
- Schema Imports:

  Lighthouse reads your schema from a single entrypoint, in this case `schema.graphql`. You can import other schema files from there to split up your schema into multiple files.
  
  [Imports](https://lighthouse-php.com/4.6/digging-deeper/schema-organisation.html#schema-imports) always begin on a separate line with `#import`, followed by the relative path to the imported file.
  
  Suppose you created your schema files likes this:
  ```
  graphql/
      |-- schema.graphql
      |-- user.graphql
      |-- auth.graphql
  ```
  => seperate schema into multiple files:
  
 1.  `schema.graphql:`
  ```
    #import auth.graphql
    #import user.graphql
 
    type Query
    type Mutation
    type Subscription
  ```
 2.  `user.graphql:`
  ```
    type User {
        id: ID!
        name: String!
        email: String!
        password: String!
        created_at: DateTime!
        updated_at: DateTime!
        posts: [Post]! @hasMany  @with(relation: "posts")
    }

    extend type Query  @middleware(checks: ["auth:api"]) {
        users()...
    }

    extend type Mutation @middleware(checks: ["auth:api"]) {
        createUser()...
        updateUser()...
        deleteUser()...
    }
  ```
  3. `auth.graphql:`
  ```
     type AuthToken {
        token_type: String
        expires_in: Int
        access_token: String
        refresh_token: String
    }

    type LoginPayload {
        auth_token: AuthToken
        user: User
    }

    extend type Mutation {
        authenticate(
            email: String!
            password: String!
        ): LoginPayload
        @field(resolver: "App\\GraphQL\\Mutations\\AuthMutator@login")
    }
  ```
   The contents of `user.graphql` and `auth.graphql` are pasted 
   in the final schema  ( `schema.graphql` )
   
#### **5. Testing**
   - [Setup:](https://lighthouse-php.com/4.6/testing/phpunit.html#setup)
   
     Add the `MakesGraphQLRequests` trait to your test class.
     ```
     <?php

      namespace Tests;

      use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
      use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

      abstract class TestCase extends BaseTestCase
      {
          use CreatesApplication;
          use MakesGraphQLRequests;
      }

     ```
   - Running queries:
   
   	 The `graphQL()` test helper runs a query on your GraphQL endpoint and returns a TestResponse:
     ```
      public function testQueriesPosts(): void
        {
            /** @var \Illuminate\Foundation\Testing\TestResponse $response */
            $response = $this->graphQL('
            {
                posts {
                    id
                    title
                }
            }
            ');
        }
     ```
     If you want to use variables within your query, you can use the `postGraphQL()` function instead:
     ```
     public function testQueriesUser()
        {
            $user = factory(User::class)->create();
            Passport::actingAs($user);
            $response = $this->postGraphQL([
                'query' => '
                    query user($id: ID!) {
                        user(id: $id) {
                            id name email
                        }
                    }
            ',
                'variables' => [
                    'id' => $user->id
                ],
            ]);
         }
     ```
     Assertions: make sure the returned results match our expectations.
     The `assertJson()` method asserts that the response is a superset of the given JSON.
     ```
     public function testQueriesUser()
        {
            $user = factory(User::class)->create();
            Passport::actingAs($user);
            $response = $this->postGraphQL([
                'query' => '
                    query user($id: ID!) {
                        user(id: $id) {
                            id name email
                        }
                    }
            ',
                'variables' => [
                    'id' => $user->id
                ],
            ]);

            $response->assertJson([
                "data" => [
                    "user" => [
                        "id" => $user->id,
                        "name" => $user->name,
                        "email" => $user->email
                    ]
                ]
            ]);
        }
     ```
     
   - HTTP Tests:
   
     ...
  
#### **6. Playground**
   - `Login user`: 
   
     Input:
     ```
      mutation {
          login(input: { username: "admin@gmail.com", password: "secret" }) {
            access_token
            refresh_token
            token_type
            user {
              id
              name
              email
            }
          }
        }
     ```
     Output:
     ```
     {
       "data": {
         "login": {
           "access_token":"eyJ0eXA...asdfdf",
           "refresh_token": "def50...200cd",
           "token_type": "Bearer",
           "user": {
             "id": "1",
             "name": "Admin Vccorp",
             "email": "admin@gmail.com"
           }
         }
       }
     }
     ```
     ---+++---
     
     `Except login route, all routes require authentication using jwt (passing token):`
    
        ```
        HTTP Headers:
          {
            "Authorization": "Bearer <access_token here>"
          }
        ```
     ---+++---
  - `Get user`:
  
    Input:
    ```
    query {
      user(id: 2) {
        id
        name
        email
      }
    }
    ```
    Output:
    ```
    {
      "data": {
        "user": {
          "id": "2",
          "name": "Test",
          "email": "test@gmail.com"
        }
      }
    }
    ```
  - `Create user`:
   
    Input:
    ```
    mutation{
      createUser(name:"testcreate", email:"user@gmail.com", password:"secret"){
        id name email 
      }
    }
    ```
    Output:
    ```
    {
      "data": {
        "createUser": {
          "id": "21",
          "name": "testcreate",
          "email": "user@gmail.com"
        }
      }
    }
    ```
  - `Update user`:
  
    Input:
    ```
    mutation {
      updateUser(id: 3, name: "update name") {
        id
        name
        email
      }
    }
    ```
    Output:
    ```
    {
      "data": {
        "updateUser": {
          "id": "3",
          "name": "update name",
          "email": "updateUser@gmail.com"
        }
      }
    }
    ```
  - `Delete user`:
     
     Input:
     ```
     mutation {
       deleteUser(id: 19) {
         id
         name
         email
         deleted_at
       }
     }
     ```
     
     Output:
     ```
     {
       "data": {
         "deleteUser": {
           "id": "19",
           "name": "test-create-user",
           "email": "user@gmail.com",
           "deleted_at": "2019-11-12 09:09:18"
         }
       }
     }
     ```
  
#### **7. Api reference**

`php artisan lighthouse:clear-cache`

`php artisan lighthouse:interface <Interface name>`

`php artisan lighthouse:mutation <Mutation name>`

`php artisan lighthouse:query <Query name>`

`@field`
```
Assign a resolver function to a field.
Pass a class and a method to the resolver argument and separate them with an @ symbol. If you pass only a class name, the method name defaults to __invoke.
```

`@find`
```
Find a model based on the arguments provided.
type Query {
    userById(id: ID! @eq): User @find
}
(more than one result is returned)
```

`@first`
```
Get the first query result from a collection of Eloquent models.
type Query {
    userByFirstName(first_name: String! @eq): User @first
}
(first_name is a column in db)
```

`@eq`
```
Place an equal operator on an Eloquent query.
query{
user(id: ID @eq): User @find  
}
(find by column "id" in db)
If the name of the argument does not match the database column, pass the actual column name as the key.
query{
user(masosinhvien: ID @eq(key:"id")): User @find  
}
```

`@middleware`
```
Run Laravel middleware for a specific field. This can be handy to reuse existing middleware.

type Query @middleware(checks: ["auth:api"]) {
    # This field will use the "auth:api" middleware
    users: [User!]! @all
}

extend type Query {
    # This field will not use any middleware
    posts: [Post!]! @all
}
```

`@orderBy`
```
Sort a result list by one or more given fields.
type Query {
    posts(orderBy: [OrderByClause!] @orderBy): [Post!]!
}
input OrderByClause{
    field: String!
    order: SortOrder!
}

enum SortOrder {
    ASC
    DESC
}
```

`@paginate`
```
Query multiple entries as a paginated list.
```

`@rules`
```
Validate an argument using Laravel built-in validation.
```

`@scalar`
```
scalar DateTime @scalar(class: "DateTimeScalar")
Reference a class implementing a scalar definition
GraphQL specification describes several built-in scalar types. In graphql-php they are exposed as static methods of GraphQL\Type\Definition\Type class:

Type::string();  // String type
Type::int();     // Int type
Type::float();   // Float type
Type::boolean(); // Boolean type
Type::id();      // ID type
```

`@subscription`
```
Reference a class to handle the broadcasting of a subscription to clients.
```

`@where`

`@with`
```
Eager loading
```