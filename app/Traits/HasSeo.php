<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait for models with SEO meta fields.
 * Expects: seo_title, seo_description, og_image columns on the model.
 */
trait HasSeo
{
    public function getSeoTitle(): string
    {
        return $this->seo_title ?: $this->getDefaultSeoTitle();
    }

    public function getSeoDescription(): string
    {
        return $this->seo_description ?: $this->getDefaultSeoDescription();
    }

    public function getOgImage(): ?string
    {
        return $this->og_image ?: $this->getDefaultOgImage();
    }

    /**
     * Override in model to provide a default SEO title.
     */
    protected function getDefaultSeoTitle(): string
    {
        return $this->title ?? $this->name ?? config('marketplace.name');
    }

    /**
     * Override in model to provide a default SEO description.
     */
    protected function getDefaultSeoDescription(): string
    {
        if (isset($this->description)) {
            return Str::limit(strip_tags($this->description), 160);
        }

        return config('marketplace.seo.default_description');
    }

    /**
     * Override in model to provide a default OG image.
     */
    protected function getDefaultOgImage(): ?string
    {
        return config('marketplace.seo.og_image');
    }
}
