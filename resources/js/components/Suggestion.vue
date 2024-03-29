<template>
    <div class="v-suggestions">
        <p class="control has-icons-left has-icons-right">
            <input type="text"
                   v-bind="$attrs"
                   :class="extendedOptions.inputClass"
                   :placeholder="extendedOptions.placeholder"
                   autocomplete="false"
                   @keydown="onKeyDown"
                   @blur="hideItems"
                   @focus="showItems = true"
                   v-model="query"/>
            <span class="icon is-medium is-left">
                <i class="fas fa-search"></i>
            </span>
            <span v-show="isLoading" class="icon is-medium is-right">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </p>
        <div class="suggestions">
            <ul class="items" v-show="isShowItems()">
                <li class="item"
                    v-for="(item, index) in items"
                    :key="index"
                    @click.prevent="selectItem(index)"
                    :class="{'is-active': isCurrentItem(index)}">
                    <slot name="item" :item="item">{{item}}</slot>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Suggestion",
        props: {
            options: {
                type: Object,
                default: {}
            },
            value: {
                type: String,
                require: true
            },
            onInputChange: {
                type: Function,
                require: true
            },
            onItemSelected: {
                type: Function
            },
            isLoading: {
                type: Boolean,
                require: true
            }
        },
        data() {
            const defaultOptions = {
                debounce: 0,
                placeholder: '',
                inputClass: 'input'
            }
            const extendedOptions = Object.assign({}, defaultOptions, this.options)

            return {
                extendedOptions,
                query: this.value,
                items: [],
                lastSetQuery: null,
                activeItemIndex: -1,
                showItems: false,
            }
        },
        beforeMount() {
            if (this.extendedOptions.debounce !== 0) {
                this.onQueryChanged = _.debounce(this.onQueryChanged, this.extendedOptions.debounce)
            }
        },
        watch: {
            'query': function (newValue, oldValue) {
                if (newValue === this.lastSetQuery) {
                    this.lastSetQuery = null
                    return
                }
                this.onQueryChanged(newValue)
                Event.fire('input', newValue)
            },
            'value': function (newValue, oldValue) {
                this.setInputQuery(newValue)
            }
        },
        methods: {
            setInputQuery(value) {
                this.lastSetQuery = value
                this.query = value
            },
            onQueryChanged(value) {
                const result = this.onInputChange(value)

                this.items = []
                if (typeof result === 'undefined' || typeof result === 'boolean' || result === null) {
                    return
                }
                if (result instanceof Array) {
                    this.setItems(result)
                } else if (typeof result.then === 'function') {
                    result.then(items => {
                        this.setItems(items)
                    })
                }
            },
            setItems(items) {
                this.items = items
                this.activeItemIndex = -1
                this.showItems = true
            },
            isShowItems() {
                return this.items.length > 0 && this.showItems === true
            },
            isCurrentItem(currentIndex) {
                return currentIndex === this.activeItemIndex
            },
            hideItems() {
                setTimeout(() => {
                    this.showItems = false
                }, 300)
            },
            onKeyDown (e) {
                switch (e.keyCode) {
                    case 40:
                        this.highlightItem('down')
                        e.preventDefault()
                        break
                    case 38:
                        this.highlightItem('up')
                        e.preventDefault()
                        break
                    case 13:
                        this.selectItem()
                        e.preventDefault()
                        break
                    case 27:
                        this.showItems = false
                        e.preventDefault()
                        break
                    default:
                        return true
                }
            },

            highlightItem (direction) {
                if (this.items.length === 0) {
                    return
                }
                let selectedIndex = this.items.findIndex((item, index) => {
                    return index === this.activeItemIndex
                })
                if (selectedIndex === -1) {
                    // nothing selected
                    if (direction === 'down') {
                        selectedIndex = 0
                    } else {
                        selectedIndex = this.items.length - 1
                    }
                } else {
                    this.activeIndexItem = 0
                    if (direction === 'down') {
                        selectedIndex++
                        if (selectedIndex === this.items.length) {
                            selectedIndex = 0
                        }
                    } else {
                        selectedIndex--
                        if (selectedIndex < 0) {
                            selectedIndex = this.items.length - 1
                        }
                    }
                }
                this.activeItemIndex = selectedIndex
            },
            selectItem (index) {
                let item = null
                if (typeof index === 'undefined') {
                    if (this.activeItemIndex === -1) {
                        return
                    }
                    item = this.items[this.activeItemIndex]
                } else {
                    item = this.items[index]
                }
                if (!item) {
                    return
                }
                if (this.onItemSelected) {
                    this.onItemSelected(item)
                } else {
                    this.onItemSelectedDefault(item)
                }
                this.hideItems()
            },
            onItemSelectedDefault (item) {
                if (typeof item === 'string') {
                    this.$emit('input', item)
                    this.setInputQuery(item)
                    this.showItems = false
                    // console.log('change value')
                }
            }
        }
    }
</script>

<style>
    .v-suggestions {
        position: relative;
    }

    .v-suggestions .suggestions {
        position: absolute;
        left: 0;
        top: 100%;
        z-index: 100;
        width: 100%;
        background: #ffffff;
        border-radius: 5px;
    }

    .v-suggestions .items {
        list-style: none;
        border: 1px solid #EEE;
        margin: 0;
        padding: 0;
        border-width: 0 1px 1px 1px;
        border-radius: 5px;
    }

    .v-suggestions .item {
        border-bottom: 1px solid #eee;
        padding: .4rem;
        color: #000;
    }

    .v-suggestions .items .item.is-active, .v-suggestions .items .item:hover {
        background: #eee;
        cursor: pointer;
        border-radius: 5px;
    }

    .v-suggestions-input {
        -webkit-appearance: none;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        border: 1px solid transparent;
        border-radius: 3px;
        box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.16), 0 0 0 1px rgba(0, 0, 0, 0.08);
        display: -webkit-inline-box;
        display: -ms-inline-flexbox;
        display: inline-flex;
        font-size: 1rem;
        height: 2.25em;
        -webkit-box-pack: start;
        -ms-flex-pack: start;
        justify-content: flex-start;
        line-height: 1.5;
        padding-bottom: calc(0.375em - 1px);
        padding-left: calc(0.625em - 1px);
        padding-right: calc(0.625em - 1px);
        padding-top: calc(0.375em - 1px);
        position: relative;
        vertical-align: top;
        background-color: white;
        border-color: #dbdbdb;
        color: #363636;
        max-width: 100%;
        width: 100%;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        position: relative;
    }

    .v-suggestions-input:focus, .v-suggestions-input:active {
        box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16), 0 0 0 1px rgba(0, 0, 0, 0.08);
        outline: none;
    }

</style>
