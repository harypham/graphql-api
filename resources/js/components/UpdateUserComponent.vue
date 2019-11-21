<template>
    <div class="flex flex-col items-center p-8">
        <div class="p-8">
            <p>---Mutation update user trigger here---</p>
            <label>User updated:</label> <span>{{ user }}</span>
        </div>
    </div>
</template>

<script>
    import gql from 'graphql-tag';
    export default {
        data() {
            return {
                user: '',
            };
        },
        mounted() {
            console.log("user update component");
        },

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
                        this.user = data.userUpdated;
                        console.log(data);
                    },
                },
            },
        },
    };
</script>

<style scoped></style>
